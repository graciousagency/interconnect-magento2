<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Address;
use Gracious\Interconnect\Http\Request\Data\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Throwable;

class CustomerSaveAfterDataObject extends Observer
{

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->config->isComplete()) {
            $this->logger->error(__METHOD__ . ' :: Unable to start: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        try {
            $customer = new Customer();
            $this->client->sendData(
                $customer->setupData($observer->getData('customer_data_object')),
                InterconnectClient::ENDPOINT_CUSTOMER_REFRESH
            );
        } catch (Throwable $exception) {
            $this->logger->exception($exception);
            return;
        }

        
    }
}
