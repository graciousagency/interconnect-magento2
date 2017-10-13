<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Gracious\Interconnect\Support\ModelInspector;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Customer\Factory as CustomerDataFactory;

/**
 * Class CustomerObserver
 * @package Gracious\Interconnect\Observer
 * Handles an event following a create or update event for customer data
 */
class CustomerSaveCommitAfterEventObserver extends ObserverAbstract
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

        /* @var $customer Customer */ $customer = $observer->getEvent()->getData('customer');
        $modelInspector = new ModelInspector($customer);

        if(!$modelInspector->isNew()) {
            $this->logger->notice('Customer(id='.$customer->getId().') is not new, aborting...');

            return;
        }

        $customerDataFactory = new CustomerDataFactory();

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try{
            $data = $customerDataFactory->setupData($customer);
        }catch (Throwable $exception) {
            $this->logger->error('Failed to prepare the customer data. *** MESSAGE ***:  '.$exception->getMessage().',  *** TRACE ***:'.$exception->getTraceAsString());

            return;
        }

        $this->logger->notice('Customer data: ' . json_encode($data));

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try {
            $this->client->sendData($data, InterconnectClient::ENDPOINT_CUSTOMER);
        }catch(Throwable $exception) {
            $this->logger->error('Failed to send customer. *** MESSAGE ***: '.$exception->getMessage().', *** TRACE ***: '.$exception->getTraceAsString());
        }
    }
}