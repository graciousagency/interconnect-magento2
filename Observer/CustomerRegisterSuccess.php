<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Customer as CustomerData;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer as EventObserver;
use Throwable;

/**
 * Class CustomerRegisterSuccessEventObserver
 * @package Gracious\Interconnect\Observer
 */
class CustomerRegisterSuccess extends Observer
{

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->config->isComplete()) {
            $this->logger->error(__METHOD__ . ' :: Unable to start: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        /* @var $customer Customer */
        $customer = $observer->getEvent()->getData('customer');
        $customerData = new Customer();

        try {
            $requestData = $customerData->setupData($customer);
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_CUSTOMER);
        } catch (Throwable $exception) {
            $this->logger->exception($exception);

            return;
        }

        $this->logger->info(__METHOD__ . ' :: Customer sent to Interconnect (' . $customer->getId() . ')');
    }
}