<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Subscriber\Factory as SubscriberDataFactory;
use Magento\Framework\Event\Observer;
use Magento\Newsletter\Model\Subscriber;
use Throwable;

/**
 * Class NewsletterManageSaveEventObserver
 * @package Gracious\Interconnect\Observer
 */
class NewsletterSubscriberSaveCommitAfterEventObserver extends ObserverAbstract
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

        /* @var $subscriber Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        $subscriberDataFactory = new SubscriberDataFactory();

        try {
            $requestData = $subscriberDataFactory->setupData($subscriber);
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_NEWSLETTER_SUBSCRIBER);
        } catch (Throwable $exception) {
            $this->logger->exception($exception);

            return;
        }

        $this->logger->info(__METHOD__ . ' :: Subscriber sent to Interconnect (' . $subscriber->getId() . ')');
    }
}