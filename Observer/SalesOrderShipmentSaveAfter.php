<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client;
use Gracious\Interconnect\Http\Request\Data\Shipment;
use Magento\Framework\Event\Observer as EventObserver;
use Throwable;

class SalesOrderShipmentSaveAfter extends Observer
{

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $shipmentData = new Shipment();

        try {
            $this->client->sendData(
                $shipmentData->setupData($observer->getShipment()),
                Client::ENDPOINT_INVOICE
            );
        } catch (Throwable $exception) {
            $this->logger->exception($exception);
            return;
        }
    }
}
