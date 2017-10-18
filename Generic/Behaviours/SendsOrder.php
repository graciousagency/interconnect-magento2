<?php
namespace Gracious\Interconnect\Generic\Behaviours;

use Throwable;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;

/**
 * Trait SendsOrder
 * @package Gracious\Interconnect\Generic\Behaviours
 */
trait SendsOrder
{

    /**
     * @param Order $order
     * @param LoggerInterface $logger
     * @param InterconnectClient $client
     */
    public function sendOrder(Order $order, LoggerInterface $logger, InterconnectClient $client) {
        $orderDataFactory = new OrderDataFactory();

        try {
            $requestData = $orderDataFactory->setupData($order);
        }catch (Throwable $exception) {
            $logger->error('Failed to prepare the order data. *** MESSAGE ***:  '.$exception->getMessage().' *** TRACE ****: '.$exception->getTraceAsString());

            return;
        }

        $logger->debug('Order data: ' . json_encode($requestData));

        // Using try/catch because we don't want this to interfere with critical logic (for example: crash the checkout so that orders can not be placed)
        try {
            $client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
        }catch (Throwable $exception) {
            $logger->error('Failed to send order. *** MESSAGE ***: '.$exception->getMessage().' *** TRACE ****: '.$exception->getTraceAsString());

            return;
        }

        $logger->info(__METHOD__.' :: Order sent to Interconnect ('.$order->getId().')');
    }
}