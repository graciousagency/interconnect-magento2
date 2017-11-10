<?php
namespace Gracious\Interconnect\Http\Request\Data\Order\Item;

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
 * @package Gracious\Interconnect\Http\Request\Data\Order\Item
 */
class Factory extends FactoryAbstract
{

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * Factory constructor.
     * @param Order $order
     */
    public function __construct()
    {
        $this->imageHelper = ObjectManager::getInstance()->create(ImageHelper::class);

        parent::__construct();
    }

    /**
     * @return array
     */
    public function setupData(Order $order) {
        $rows = [];
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            /* @var $orderItem OrderItem */
            /* @var $product Product */
            $product = $orderItem->getProduct();

            if($product !== null) { // Redundancy (Could $product be null?)
                $productTypeId = $product->getTypeId();
//                $this->logger->debug('$productTypeId : '.$productTypeId);

                switch ($productTypeId) {
                    case ProductType::SIMPLE:
                    case ProductType::VIRTUAL:
                    case ProductType::DOWNLOADABLE:
                        $rows[] = $this->setupOrderItemData($order, $orderItem, $product);

                        break;
                }
            }
        }

        return $rows;
    }

    /**
     * @param OrderItem $orderItem
     * @return string[]
     */
    protected function setupOrderItemData(Order $order, OrderItem $orderItem, Product $product)
    {
        $image = $this->imageHelper->init($product, 'category_page_list')->getUrl();

        return [
            'emailAddress'      => $order->getCustomerEmail(),
            'orderId'           => $this->generateEntityId($order->getId(),EntityType::ORDER),
            'itemId'            => $this->generateEntityId($orderItem->getItemId(), EntityType::ORDER_ITEM),
            'incrementId'       => $order->getIncrementId(),
            'productId'         => $this->generateEntityId($product->getId(),EntityType::PRODUCT),
            'productName'       => $product->getName(),
            'productSKU'        => $product->getSku(),
            'category'          => $this->getCategoryNameByProduct($product),
            'subCategory'       => null,
            'quantity'          => (int)$orderItem->getQtyOrdered(),
            'priceInCents'      => PriceCents::create($product->getPrice())->toInt(),
            'totalPriceInCents' => PriceCents::create($orderItem->getQtyOrdered() * $product->getPrice())->toInt(),
            'orderedAtISO8601'  => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'productUrl'        => $product->getProductUrl(),
            'productImageUrl'   => $image
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