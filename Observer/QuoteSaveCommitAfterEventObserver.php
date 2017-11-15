<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Quote\Factory as QuoteDataFactory;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Throwable;

/**
 * Class QuoteObserver
 * @package Gracious\Interconnect\Observer
 * Sends a quote to our webservice on create or update
 *
 * THIS OBSERVER IS CURRENTLY NOT ACTIVE (SEE: EVENTS.XML) BECAUSE IT'S NOT IMPLEMENTED IN THE INTERCONNECT WEB SERVICE YET.
 */
class QuoteSaveCommitAfterEventObserver extends ObserverAbstract
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

        /** * @var $quote Quote */
        $quote = $observer->getQuote();
        $quoteDataFactory = new QuoteDataFactory();

        try {
            $requestData = $quoteDataFactory->setupData($quote);
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_QUOTE);
        } catch (Throwable $exception) {
            $this->logger->exception($exception);

            return;
        }

        $this->logger->info(__METHOD__ . ' :: Quote sent to Interconnect (' . $quote->getId() . ')');
    }
}