<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Address;
use Magento\Framework\Event\Observer as EventObserver;
use Throwable;

class CustomerAddressSaveAfter extends Observer
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

        $customerAddress = $observer->getEvent()->getData('customer_address');
        $email = $observer->getEvent()->getData()['customer_address']->getCustomer()->getEmail();

        try {
            $address = new Address();
            if ($observer->getEvent()->getData('customer_address')->getData('is_default_billing')) {
                $this->client->sendData(
                    array_replace_recursive(
                        ['emailAddress' => $email],
                        $address->setupData($customerAddress)
                    ),
                    InterconnectClient::ENDPOINT_REGISTER_BILLING_ADDRESS
                );
            }

            if ($observer->getEvent()->getData('customer_address')->getData('is_default_shipping')) {
                $this->client->sendData(
                    array_replace_recursive(
                        ['email' => $email],
                        $address->setupData($customerAddress)
                    ),
                    InterconnectClient::ENDPOINT_REGISTER_SHIPPING_ADDRESS
                );
            }

        } catch (Throwable $exception) {
            $this->logger->exception($exception);
            return;
        }

        $this->logger->info(__METHOD__ . ' :: Addresses sent to Interconnect (' . $email . ')');
    }
}
