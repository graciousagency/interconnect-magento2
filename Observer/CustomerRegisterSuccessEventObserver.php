<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Customer\Factory as CustomerDataFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Throwable;

/**
 * Class CustomerRegisterSuccessEventObserver
 * @package Gracious\Interconnect\Observer
 */
class CustomerRegisterSuccessEventObserver extends ObserverAbstract
{

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isComplete()) {
            $this->logger->error(__METHOD__ . ' :: Unable to start: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        /* @var $customer Customer */
        $customer = $observer->getEvent()->getData('customer');
        $customerDataFactory = new CustomerDataFactory();

        try {
            $requestData = $customerDataFactory->setupData($customer);
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_CUSTOMER);
        } catch (Throwable $exception) {
            $this->logger->exception($exception);

            return;
        }

        $this->logger->info(__METHOD__ . ' :: Customer sent to Interconnect (' . $customer->getId() . ')');
    }
}