<?php
namespace Gracious\Interconnect\Http\Request\Data\Order;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\ObjectFactory;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\ProductType;
use Gracious\Interconnect\Support\PaymentStatus;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Sales\Model\Order\Item as OrderItem;
use Gracious\Interconnect\Support\Text\Inflector;
use Gracious\Interconnect\Reflection\OrderReflector;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;
use Gracious\Interconnect\Http\Request\Data\Customer\Factory as CustomerFactory;
use Gracious\Interconnect\Http\Request\Data\Order\Item\Factory as OrderItemFactory;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Order
 */
class Factory extends FactoryAbstract
{
    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $this->imageHelper = ObjectManager::getInstance()->create(ImageHelper::class);

        parent::__construct();
    }

    /**
     * @param Order $order
     * @return array
     */
    public function setupData(Order $order) {



        $quoteId = $order->getQuoteId();
        $prefixedQuoteId = $quoteId !== null ? $this->generateEntityId($quoteId,EntityType::QUOTE) : null;
        $orderItemFactory = new OrderItemFactory();
        $paymentMethod = Inflector::unSnakeCase($order->getPayment()->getMethod());
        $paymentMethod = ucwords($paymentMethod);
        $total = $order->getBaseGrandTotal();
        $discountAmount = $order->getDiscountAmount();
        $discountPercentage = ($discountAmount !== null && $discountAmount > 0 && $total !== null && $total > 0) ? (($discountAmount / $total) * 100): 0;

        return [
            'orderId'               => $this->generateEntityId($order->getId(), EntityType::ORDER),
            'quoteId'               => $prefixedQuoteId,
            'incrementId'           => $order->getIncrementId(),
            'quantity'              => (int)$order->getTotalQtyOrdered(),
            'totalAmountInCents'    => PriceCents::create($total)->toInt(),
            'discountAmountInCents' => PriceCents::create($discountAmount)->toInt(),
            'discountPercentage'    => round($discountPercentage, 2),
            'discountType'          => $order->getDiscountDescription(),
            'paymentStatus'         => $this->getPaymentStatus($order),
            'orderStatus'           => ucfirst($order->getState()),
            'shipmentStatus'        => $this->getOrderShipmentStatus($order),
            'couponCode'            => $order->getCouponCode(),
            'paymentMethod'         => $paymentMethod,
            'emailAddress'          => $order->getCustomerEmail(),
            'customer'              => $this->getOrderCustomerData($order),
            'orderedAtISO8601'      => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'updatedAt'             => Formatter::formatDateStringToIso8601($order->getUpdatedAt()),
            'createdAt'             => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'items'                 => $orderItemFactory->setupData($order)
        ];
    }

    /**
     * @param Order $order
     * @return string
     */
    protected function getPaymentStatus(Order $order) {
        /* @var $orderInspector OrderReflector */ $orderInspector =  ObjectManager::getInstance()->create(OrderReflector::class);
        $paymentStatus = $orderInspector->getOrderPaymentStatus($order);

        return ucwords(Inflector::unSnakeCase($paymentStatus));
    }

    /**
     * @param Order $order
     * @return string
     * Returns the shipping status of an order as a string, not a constant.
     */
    protected function getOrderShipmentStatus(Order $order) {
        $shipments = $order->getShipmentsCollection();

        // Determining shipment status in Magento is quite complex because an order can have multiple shipments and can also contain virtual and downloadable products.

        // !!!: This if-statement has been intentionally placed above the !$order->canShip() check because that can return false, even when there are shipments (possibly because the order can't ship as it has already been shipped?)
        if($shipments->count() > 0) {
            return 'Shipped'; // consider order partially shipped at this moment.
        }

        if(!$order->canShip()) {
            // Doesn't always mean an order doesn't have shippable items; it can also be possible there are other reasons it won't ship.

            return 'Won\'t Ship';
        }

        return 'Not Shipped';
    }

    /**
     * @param Order $order
     * @return array
     */
    protected function getOrderCustomerData(Order $order) {
        $customerData = null;
        $customerFactory = new CustomerFactory();

        if($order->getCustomerIsGuest()) {
            return $customerFactory->setUpAnonymousCustomerDataFromOrder($order);
        }

        $customer = $this->getOrderCustomer($order);

        if($customer === null) {
            return $customerFactory->setUpAnonymousCustomerDataFromOrder($order);
        }

        return $customerFactory->setupData($customer);
    }

    /**
     * @param Order $order
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer|null
     */
    protected function getOrderCustomer(Order $order) {
        $customer = $order->getCustomer();

        if($customer !== null) {
            return $customer;
        }

        // Order object does not have a customer but it still might be in the database. Weird, but this happens
        /* @var $customerRepository CustomerRepositoryInterface */ $customerRepository = ObjectManager::getInstance()->create(CustomerRepositoryInterface::class);

        try {
            $customer = $customerRepository->getById($order->getCustomerId());
        }catch (Exception $exception) {
            return null;
        }

        return $customer;
    }
}