<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Subscriber as SubscriberData;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Newsletter\Model\Subscriber;
use Throwable;

/**
 * Class NewsletterManageSaveEventObserver
 * @package Gracious\Interconnect\Observer
 */
class NewsletterSubscriberSaveCommitAfter extends Observer
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

        /* @var $subscriber Subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        $subscriberDataFactory = new SubscriberData();

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