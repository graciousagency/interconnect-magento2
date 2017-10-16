<?php
namespace Gracious\Interconnect\Http\Request\Data\Order;

use Exception;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\ProductType;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Sales\Model\Order\Item as OrderItem;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;
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

        return [
            'orderId'               => $this->generateEntityId($order->getId(), EntityType::ORDER),
            'quoteId'               => $prefixedQuoteId,
            'incrementId'           => $order->getIncrementId(),
            'totalAmountInCents'    => PriceCents::create($order->getBaseGrandTotal())->toInt(),
            'quantity'              => (int)$order->getTotalQtyOrdered(),
            'couponCode'            => $order->getCouponCode(),
            'emailAddress'          => $order->getCustomerEmail(),
            'orderedAtISO8601'      => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'updatedAt'             => Formatter::formatDateStringToIso8601($order->getUpdatedAt()),
            'createdAt'             => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'items'                 => $orderItemFactory->setupData($order)
        ];
    }
}