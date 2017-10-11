<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;
use Gracious\Interconnect\Support\ModelInspector;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;

/**
 * Class OrderObserver
 * @package Gracious\Interconnect\Observer
 */
class OrderSaveCommitAfterEventObserver extends ObserverAbstract
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

        /** * @var $order Order */ $order = $observer->getOrder();
        $modelInspector = new ModelInspector($order);

        if(!$modelInspector->isNew()) {
            $this->logger->debug('Aborting send process; this order is not a new order (id '.$order->getId().') ....');

            return;
        }

        $orderDataFactory = new OrderDataFactory();

        try {
            $requestData = $orderDataFactory->setupData($order);
        }catch (Throwable $exception) {
            $this->logger->error('Failed to prepare the order data. *** MESSAGE ***:  '.$exception->getMessage().',  *** TRACE ***: '.$exception->getTraceAsString());

            return;
        }

        $this->logger->debug('Order data: ' . json_encode($requestData));
        
        // Using try/catch because we don't want this to interfere with critical logic (for example: crash the checkout so that orders can not be placed)
        try {
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
        }catch (Throwable $exception) {
            $this->logger->error('Failed to send order. *** MESSAGE ***: '.$exception->getMessage().', *** TRACE ***: '.$exception->getTraceAsString());
        }
    }
}