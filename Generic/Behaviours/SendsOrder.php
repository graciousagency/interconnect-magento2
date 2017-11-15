<?php

namespace Gracious\Interconnect\Generic\Behaviours;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;
use Gracious\Interconnect\Reporting\Logger;
use Magento\Sales\Model\Order;
use Throwable;

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
    public function sendOrder(Order $order, Logger $logger, InterconnectClient $client)
    {
        $orderDataFactory = new OrderDataFactory();

        try {
            $requestData = $orderDataFactory->setupData($order);
            $client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
        } catch (Throwable $exception) {
            $logger->exception($exception);

            return;
        }
    }
}