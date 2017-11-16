<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\ProductType;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Order\Item
 */
class OrderItem extends Data
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
    public function setupData(Order $order)
    {
        $rows = [];
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            /* @var $orderItem Item */
            /* @var $product Product */
            $product = $orderItem->getProduct();

            if ($product !== null) {
                $productTypeId = $product->getTypeId();

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
     * @param Order $order
     * @param Item $orderItem
     * @param Product $product
     * @return string[]
     */
    protected function setupOrderItemData(Order $order, Item $orderItem, Product $product)
    {
        $image = $this->imageHelper->init($product, 'category_page_list')->getUrl();

        return [
            'emailAddress' => $order->getCustomerEmail(),
            'orderId' => $this->generateEntityId($order->getId(), EntityType::ORDER),
            'itemId' => $this->generateEntityId($orderItem->getItemId(), EntityType::ORDER_ITEM),
            'incrementId' => $order->getIncrementId(),
            'productId' => $this->generateEntityId($product->getId(), EntityType::PRODUCT),
            'productName' => $product->getName(),
            'productSKU' => $product->getSku(),
            'category' => $this->getCategoryNameByProduct($product),
            'subCategory' => null,
            'quantity' => (int)$orderItem->getQtyOrdered(),
            'priceInCents' => PriceCents::create($product->getPrice())->toInt(),
            'totalPriceInCents' => PriceCents::create($orderItem->getQtyOrdered() * $product->getPrice())->toInt(),
            'orderedAtISO8601' => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'productUrl' => $product->getProductUrl(),
            'productImageUrl' => $image
        ];
    }

    /**
     * @param $product Product
     * @return string
     */
    protected function getCategoryNameByProduct(Product $product)
    {
        $categoryNames = [];
        $categories = $product->getCategoryCollection()->addAttributeToSelect("name");

        foreach ($categories as $category) {
            /* @var $category Category */
            $categoryNames[] = $category->getName();
        }

        return implode('-', $categoryNames);
    }
}