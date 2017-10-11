<?php

namespace Gracious\Interconnect\Console;

use Gracious\Interconnect\Support\CopernicaDate;
use Gracious\Interconnect\Support\PriceCents;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Sales\Model\OrderRepository;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Psr\Log\LoggerInterface as Logger;
use Gracious\Interconnect\Helper\Config;

class SyncOrder extends Command
{


    /**
     * @var OrderRepository
     */
    private $repository;
    /**
     * @var Customer
     */
    private $entity;
    private $entityPrefix = 'Order';
    /**
     * @var Config
     */
    private $config;
    private $imageHelper;


    public function __construct(State $state, OrderRepository $repository, Order $entity, Config $config)
    {
        parent::__construct();

        $this->repository = $repository;
        $this->entity = $entity;
        $this->config = $config;
        $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Helper\Image');

    }

    protected function configure()
    {
        $this->setName('interconnect:syncorder')->setDescription('Sync an order with copernica');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'orderId', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $order = $this->repository->get($input->getOption('id'));

        $data = $this->getRequestData($order);

        $output->writeln(print_r($data,true));

    }

    public function generateEntityId($id, $entityPrefix = null){
        $entityPrefix = $entityPrefix == null ? $this->entityPrefix : $entityPrefix;
        $prefix = $this->config->getInterconnectPrefix();
        return $prefix.'-'.$entityPrefix.'-'.$id;
    }

    /**
     * @param $order Order
     * @return array
     */
    protected function getRequestData($order)
    {

        return [
            'orderId' => $this->generateEntityId($order->getId()),
            'quoteId' => $this->generateEntityId($order->getQuoteId(),'Quote'),
            'incrementId' => $order->getIncrementId(),
            'timeStamp' => CopernicaDate::create($order->getCreatedAt())->toIso(),
            'totalAmount' => PriceCents::create($order->getBaseGrandTotal())->toInt(),
            'quantity' => $order->getTotalQtyOrdered(),
            'couponCode' => $order->getCouponCode(),
            'orderRows' =>  $this->getOrderRows($order)
        ];
    }

    protected function getOrderRows($order)
    {
        $rows = [];
        foreach ($order->getAllItems() as $item) {

            if($item->getProduct()->getTypeId() == 'simple'){
                $rows[] = $this->formatOrderRow($item);

            }
        }

        return $rows;
    }

    /**
     * @param $item Order\Item
     */
    protected function formatOrderRow($item)
    {
        $image = $this->imageHelper->init($item->getProduct(),'category_page_list')->getUrl();


        return [
            'itemId'=> $this->generateEntityId($item->getId(), 'OrderItem'),
            'orderId' => $this->generateEntityId($item->getOrderId()),
            'productId' => $this->generateEntityId($item->getProductId(),'Product'),
            'incrementId' => null,
            'productName' => $this->getProductName($item->getProduct()),
            'sku' => $item->getProduct()->getSku(),
            'category' => $this->getCategoryNameByProduct($item->getProduct()),
            'subcategory' => '',
            'quantity' =>(int) $item->getQtyOrdered(),
            'price' =>   PriceCents::create($item->getProduct()->getPrice())->toInt(),
            'totalPrice' => PriceCents::create($item->getQtyOrdered() * $item->getProduct()->getPrice())->toInt(),
            'productUrl' => $item->getProduct()->getProductUrl(),
            'productImage' => $image,
        ];

    }

    function getProductName($product){

        if(!$product){
            return '';
        }

        return $product->getName();
    }

    /**
     * @param $product Product
     * @return string
     */
    protected function getCategoryNameByProduct($product){

        if(!$product){
            return '';
        }
        $categoryNames = [];
        $categories = $product->getCategoryCollection()->addAttributeToSelect("name");

        foreach($categories as $category){
            $categoryNames[] = $category->getName();
        }

        return implode('-', $categoryNames);
    }

}