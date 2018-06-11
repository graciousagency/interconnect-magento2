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
use Magento\Quote\Model\Quote as QuoteModel;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Quote
 */
class Quote extends Data
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
     * @param QuoteModel $quote
     * @return array
     */
    public function setupData(QuoteModel $quote)
    {
        return [
            'storeId' => $quote->getStoreId(),
            'quoteId' => $this->generateEntityId($quote->getId(), EntityType::QUOTE),
            'totalAmount' => PriceCents::create($quote->getBaseGrandTotal())->toInt(),
            'quantity' => (int)$quote->getItemsQty(),
            'couponCode' => $quote->getCouponCode(),
            'updatedAt' => Formatter::formatDateStringToIso8601($quote->getUpdatedAt()),
            'createdAt' => Formatter::formatDateStringToIso8601($quote->getCreatedAt()),
            'quoteRows' => $this->getQuoteRows($quote)
        ];
    }

    /**
     * @param QuoteModel $quote
     * @return array
     * @todo Do we implement any of the other product types?
     */
    protected function getQuoteRows(QuoteModel $quote)
    {
        $rows = [];
        $quoteItems = $quote->getAllItems();

        foreach ($quoteItems as $quoteItem) {
            /* @var $quoteItem Item */
            /* @var $product Product */
            $product = $quoteItem->getProduct();

            if ($product !== null) {
                $productTypeId = $product->getTypeId();

                switch ($productTypeId) {
                    case ProductType::SIMPLE:
                    case ProductType::VIRTUAL:
                    case ProductType::DOWNLOADABLE:
                        $rows[] = $this->formatQuoteRow($quote, $quoteItem, $product);
                        break;
                }
            }
        }

        return $rows;
    }

    /**
     * @param QuoteModel $quote
     * @param Item $quoteItem
     * @param Product $product
     * @return string[]
     * @internal param QuoteItem $item
     */
    protected function formatQuoteRow(QuoteModel $quote, Item $quoteItem, Product $product)
    {
        $image = $this->imageHelper->init($product, 'category_page_list')->getUrl();

        return [
            'storeId' => $quote->getStoreId(),
            'itemId' => $this->generateEntityId($quoteItem->getItemId(), EntityType::QUOTE_ITEM),
            'quoteId' => $this->generateEntityId($quote->getId(), EntityType::QUOTE),
            'productId' => $this->generateEntityId($product->getId(), EntityType::PRODUCT),
            'incrementId' => null,
            'productName' => $product->getName(),
            'sku' => $product->getSku(),
            'category' => $this->getCategoryNameByProduct($product),
            'subcategory' => null,
            'quantity' => (int)$quoteItem->getQtyOrdered(),
            'price' => PriceCents::create($product->getPrice())->toInt(),
            'totalPrice' => PriceCents::create($quoteItem->getQtyOrdered() * $product->getPrice())->toInt(),
            'productUrl' => $product->getProductUrl(),
            'productImage' => $image,
        ];
    }

    /**
     * @param Product $product
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