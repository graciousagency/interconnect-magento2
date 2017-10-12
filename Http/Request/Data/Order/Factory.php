<?php
namespace Gracious\Interconnect\Http\Request\Data\Order;

use Exception;
use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\ProductType;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Sales\Model\Order\Item as OrderItem;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;

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

        return [
            'orderId'       => $this->generateEntityId($order->getId(), EntityType::ORDER),
            'quoteId'       => $prefixedQuoteId,
            'incrementId'   => $order->getIncrementId(),
            'totalAmount'   => PriceCents::create($order->getBaseGrandTotal())->toInt(),
            'quantity'      => (int)$order->getTotalQtyOrdered(),
            'couponCode'    => $order->getCouponCode(),
            'emailAddress'  => $order->getCustomerEmail(),
            'updatedAt'     => Formatter::formatDateStringToIso8601($order->getUpdatedAt()),
            'createdAt'     => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'orderRows'     => $this->getOrderRows($order)
        ];
    }

    /**
     * @param Order $order
     * @return array
     * @todo Do we implement any of the other product types?
     */
    protected function getOrderRows(Order $order)
    {
        $rows = [];
        $orderItems = $order->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            /* @var $orderItem OrderItem */
            /* @var $product Product */ $product = $orderItem->getProduct();

            if($product !== null) { // Don't think this can/should happen but without in-depth Magento knowledge concerning how it works now or may work in the future let's apply some redundancy here...
                $productTypeId = $product->getTypeId();
//                $this->logger->debug('$productTypeId : '.$productTypeId);

                switch ($productTypeId) {
                    case ProductType::SIMPLE:
                    case ProductType::VIRTUAL:
                    case ProductType::DOWNLOADABLE:
                        $rows[] = $this->formatOrderRow($order, $orderItem, $product);

                        break;
                }
            }
        }

        return $rows;
    }

    /**
     * @param Order $order
     * @param OrderItem $orderItem
     * @param Product $product
     * @return string[]
     */
    protected function formatOrderRow(Order $order, OrderItem $orderItem, Product $product)
    {
        $image = $this->imageHelper->init($product,'category_page_list')->getUrl();
        $this->logger->debug('item id for item \''. $product->getName().'\': =  '.json_encode($orderItem->getItemId()));

        return [
            'itemId'            => $this->generateEntityId($orderItem->getItemId(), EntityType::ORDER_ITEM),
            'orderId'           => $this->generateEntityId($order->getId(),EntityType::ORDER),
            'productId'         => $this->generateEntityId($product->getId(),EntityType::PRODUCT),
            'productName'       => $product->getName(),
            'sku'               => $product->getSku(),
            'category'          => $this->getCategoryNameByProduct($product),
            'subcategory'       => null,
            'quantity'          => (int)$orderItem->getQtyOrdered(),
            'price'             => PriceCents::create($product->getPrice())->toInt(),
            'totalPrice'        => PriceCents::create($orderItem->getQtyOrdered() * $product->getPrice())->toInt(),
            'productUrl'        => $product->getProductUrl(),
            'productImage'      => $image
        ];
    }

    /**
     * @param $product Product
     * @return string
     */
    protected function getCategoryNameByProduct(Product $product){
        $categoryNames = [];
        $categories = $product->getCategoryCollection()->addAttributeToSelect("name");

        foreach($categories as $category) {
            /* @var $category Category */
            $categoryNames[] = $category->getName();
        }

        return implode('-', $categoryNames);
    }
}