<?php
namespace Gracious\Interconnect\Generic\Behaviours;

use Throwable;
use Magento\Sales\Model\Order;
use Gracious\Interconnect\Reporting\Logger;
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
     * @param Logger $logger
     * @param InterconnectClient $client
     */
    public function sendOrder(Order $order, Logger $logger, InterconnectClient $client) {
        $orderDataFactory = new OrderDataFactory();

        try {
            $requestData = $orderDataFactory->setupData($order);
        }catch (Throwable $exception) {
            $logger->exception($exception);

            return;
        }

        // Using try/catch because we don't want this to interfere with critical logic (for example: crash the checkout so that orders can not be placed)
        try {
            $client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
        }catch (Throwable $exception) {
            $logger->exception($exception);

            return;
        }
    }
}