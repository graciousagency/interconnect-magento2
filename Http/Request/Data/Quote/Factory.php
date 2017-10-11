<?php
namespace Gracious\Interconnect\Http\Request\Data\Quote;

use Magento\Quote\Model\Quote;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\Support\ProductType;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Quote
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

    public function setupData(Quote $quote) {
        return [
            'quoteId'           => $this->generateEntityId($quote->getId(), EntityType::QUOTE),
            'totalAmount'       => PriceCents::create($quote->getBaseGrandTotal())->toInt(),
            'quantity'          => $quote->getItemsQty(),
            'couponCode'        => $quote->getCouponCode(),
            'updatedAt'         => Formatter::formatDateStringToIso8601($quote->getUpdatedAt()),
            'createdAt'         => Formatter::formatDateStringToIso8601($quote->getCreatedAt()),
            'quoteRows'         => $this->getQuoteRows($quote)
        ];
    }

    /**
     * @param Quote $quote
     * @return array
     * @todo Do we implement any of the other product types?
     */
    protected function getQuoteRows(Quote $quote)
    {
        $rows = [];
        $quoteItems = $quote->getAllItems();

        foreach ($quoteItems as $quoteItem) {
            /* @var $quoteItem QuoteItem */
            /* @var $product Product */ $product = $quoteItem->getProduct();

            if($product !== null) { // Don't think this can/should happen but without in-depth Magento knowledge concerning how it works now or may work in the future let's apply some redundancy here...
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
     * @param Quote $quote
     * @param QuoteItem $item
     * @param Product $product
     * @return string[]
     */
    protected function formatQuoteRow(Quote $quote, QuoteItem $quoteItem, Product $product)
    {
        $image = $this->imageHelper->init($product,'category_page_list')->getUrl();

        return [
            'itemId'            => $this->generateEntityId($quoteItem->getItemId(), EntityType::QUOTE_ITEM),
            'quoteId'           => $this->generateEntityId($quote->getId(),EntityType::QUOTE),
            'productId'         => $this->generateEntityId($product->getId(),EntityType::PRODUCT),
            'incrementId'       => null,
            'productName'       => $product->getName(),
            'sku'               => $product->getSku(),
            'category'          => $this->getCategoryNameByProduct($product),
            'subcategory'       => null,
            'quantity'          => (int)$quoteItem->getQtyOrdered(),
            'price'             => PriceCents::create($product->getPrice())->toInt(),
            'totalPrice'        => PriceCents::create($quoteItem->getQtyOrdered() * $product->getPrice())->toInt(),
            'productUrl'        => $product->getProductUrl(),
            'productImage'      => $image,
        ];
    }

    /**
     * @param Product $product
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