<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Customer\Factory as CustomerDataFactory;

/**
 * Class CustomerRegisterSuccessEventObserver
 * @package Gracious\Interconnect\Observer
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
        $customerDataFactory = new CustomerDataFactory();

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try{
            $requestData = $customerDataFactory->setupData($customer);
        }catch (Throwable $exception) {
//            $this->logger->error('Failed to prepare the customer data. *** MESSAGE ***:  '.$exception->getMessage().',  *** TRACE ***:'.$exception->getTraceAsString());
            $this->logger->error('Failed to prepare the customer data. *** MESSAGE ***:  '.$exception->getMessage());

            return;
        }

        $this->logger->notice('Customer data: ' . json_encode($requestData));

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try {
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_CUSTOMER);
        }catch(Throwable $exception) {
//            $this->logger->error('Failed to send customer. *** MESSAGE ***: '.$exception->getMessage().', *** TRACE ***: '.$exception->getTraceAsString());
            $this->logger->error('Failed to send customer. *** MESSAGE ***: '.$exception->getMessage());

            return;
        }

        $this->logger->info(__METHOD__.' :: Customer sent to Interconnect ('.$customer->getId().')');
    }
}