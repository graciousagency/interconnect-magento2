<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Data\Invoice;
use Magento\Framework\Event\Observer as EventObserver;
use Throwable;
use Gracious\Interconnect\Http\Request\Client;

class SalesOrderInvoicePay extends Observer
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $invoiceModel = $observer->getEvent()->getInvoice();

        $invoice = new Invoice();


        try {
            $invoiceData = $invoice->setupData($invoiceModel);
            $this->client->sendData($invoiceData, Client::ENDPOINT_QUOTE);
        } catch (Throwable $exception) {
            $this->logger->exception($exception);

            return;
        }

        $this->logger->info(__METHOD__ . ' :: Invoice sent to Interconnect (' . $invoice->getId() . ')');
    }
}
