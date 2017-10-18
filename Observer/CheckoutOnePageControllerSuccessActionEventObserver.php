<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderRepository;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;

/**
 * Class OrderObserver
 * @package Gracious\Interconnect\Observer
 */
class CheckoutOnePageControllerSuccessActionEventObserver extends ObserverAbstract
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if(!$this->config->isComplete()) {
            $this->logger->error(__METHOD__.' :: Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $orderIds = $observer->getDataByKey('order_ids');

        if(!is_array($orderIds) || empty($orderIds)) {
            $this->logger->alert(__METHOD__.' :: Expected to get an order id but none was provided! Aborting....');
            return;
        }

        $orderId = $orderIds[0];
        $order = $orderRepository = ObjectManager::getInstance()->create(OrderRepository::class)->get($orderId);
        $orderDataFactory = new OrderDataFactory();

        try {
            $requestData = $orderDataFactory->setupData($order);
        }catch (Throwable $exception) {
//            $this->logger->error('Failed to prepare the order data. *** MESSAGE ***:  '.$exception->getMessage().',  *** TRACE ***: '.$exception->getTraceAsString());
            $this->logger->error('Failed to prepare the order data. *** MESSAGE ***:  '.$exception->getMessage());

            return;
        }

        $this->logger->debug('Order data: ' . json_encode($requestData));
        
        // Using try/catch because we don't want this to interfere with critical logic (for example: crash the checkout so that orders can not be placed)
        try {
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
        }catch (Throwable $exception) {
//            $this->logger->error('Failed to send order. *** MESSAGE ***: '.$exception->getMessage().', *** TRACE ***: '.$exception->getTraceAsString());
            $this->logger->error('Failed to send order. *** MESSAGE ***: '.$exception->getMessage());

            return;
        }

        $this->logger->info(__METHOD__.' :: Order sent to Interconnect ('.$order->getId().')');
    }
}