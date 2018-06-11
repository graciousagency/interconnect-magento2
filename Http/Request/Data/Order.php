<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Exception;
use Gracious\Interconnect\Model\Order as InterconnectOrder;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\Text\Inflector;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order as OrderModel;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Order
 */
class Order extends Data
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    public function __construct()
    {
        $this->imageHelper = ObjectManager::getInstance()->create(ImageHelper::class);

        parent::__construct();
    }

    /**
     * @param OrderModel $order
     * @return array
     */
    public function setupData(OrderModel $order)
    {
        $quoteId = $order->getQuoteId();
        $prefixedQuoteId = $quoteId !== null ? $this->generateEntityId($quoteId, EntityType::QUOTE) : null;
        $orderItemFactory = new OrderItem();
        $paymentMethod = Inflector::unSnakeCase($order->getPayment()->getMethod());
        $paymentMethod = ucwords($paymentMethod);
        $total = $order->getGrandTotal();
        $discountAmount = $order->getDiscountAmount();
        $discountPercentage = ($discountAmount !== null && $discountAmount > 0 && $total !== null && $total > 0) ? (($discountAmount / $total) * 100) : 0;
        $couponCode = $order->getCouponCode();
        $discountType = (is_string($couponCode) && trim($couponCode)) != '' ? 'Coupon' : $order->getDiscountDescription();

        return [
            'storeId' => $order->getStoreId(),
            'orderId' => $this->generateEntityId($order->getId(), EntityType::ORDER),
            'quoteId' => $prefixedQuoteId,
            'incrementId' => $order->getIncrementId(),
            'quantity' => (int)$order->getTotalQtyOrdered(),
            'totalAmountInCents' => PriceCents::create($total)->toInt(),
            'discountAmountInCents' => PriceCents::create($discountAmount)->toInt(),
            'discountPercentage' => round($discountPercentage, 2),
            'discountType' => $discountType,
            'paymentStatus' => $this->getPaymentStatus($order),
            'orderStatus' => ucfirst($order->getState()),
            'shipmentStatus' => $this->getOrderShipmentStatus($order),
            'couponCode' => $couponCode,
            'paymentMethod' => $paymentMethod,
            'emailAddress' => $order->getCustomerEmail(),
            'customer' => $this->getOrderCustomerData($order),
            'orderedAtISO8601' => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'updatedAt' => Formatter::formatDateStringToIso8601($order->getUpdatedAt()),
            'createdAt' => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'items' => $orderItemFactory->setupData($order)
        ];
    }

    /**
     * @param OrderModel $order
     * @return string
     */
    protected function getPaymentStatus(OrderModel $order)
    {
        $interconnectOrder = new InterconnectOrder($order);
        $paymentStatus = $interconnectOrder->getOrderPaymentStatus();

        return ucwords(Inflector::unSnakeCase($paymentStatus));
    }

    /**
     * @param OrderModel $order
     * @return string
     * Returns the shipping status of an order as a string, not a constant.
     */
    protected function getOrderShipmentStatus(OrderModel $order)
    {
        $shipments = $order->getShipmentsCollection();

        // Determining shipment status in Magento is quite complex because an order can have multiple shipments and can also contain virtual and downloadable products.

        // !!!: This if-statement has been intentionally placed above the !$order->canShip() check because that can return false, even when there are shipments (possibly because the order can't ship as it has already been shipped?)
        if ($shipments->count() > 0) {
            return 'Shipped'; // consider order partially shipped at this moment.
        }

        if (!$order->canShip()) {
            // Doesn't always mean an order doesn't have shippable items; it can also be possible there are other reasons it won't ship.

            return 'Won\'t Ship';
        }

        return 'Not Shipped';
    }

    /**
     * @param OrderModel $order
     * @return array
     */
    protected function getOrderCustomerData(OrderModel $order)
    {
        $customerData = null;
        $customerFactory = new Customer();

        if ($order->getCustomerIsGuest()) {
            return $customerFactory->setUpAnonymousCustomerDataFromOrder($order);
        }

        $customer = $this->getOrderCustomer($order);

        if ($customer === null) {
            return $customerFactory->setUpAnonymousCustomerDataFromOrder($order);
        }

        return $customerFactory->setupData($customer);
    }

    /**
     * @param OrderModel $order
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer|null
     */
    protected function getOrderCustomer(OrderModel $order)
    {
        $customer = $order->getCustomer();

        if ($customer !== null) {
            return $customer;
        }

        // Order object does not have a customer but it still might be in the database. Weird, but this happens
        /* @var $customerRepository CustomerRepositoryInterface */
        $customerRepository = ObjectManager::getInstance()->create(CustomerRepositoryInterface::class);

        try {
            $customer = $customerRepository->getById($order->getCustomerId());
        } catch (Exception $exception) {
            return null;
        }

        return $customer;
    }
}