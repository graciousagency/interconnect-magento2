<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Quote\Model\Quote;
use Magento\Framework\Event\Observer;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Quote\Factory as QuoteDataFactory;

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
        if(!$this->config->isComplete()) {
            $this->logger->error(__METHOD__.' :: Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        /** * @var $quote Quote */ $quote = $observer->getQuote();
        $quoteDataFactory = new QuoteDataFactory();

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try{
            $requestData = $quoteDataFactory->setupData($quote);
        }catch (Throwable $exception) {
//            $this->logger->error('Failed to prepare the quote data. *** MESSAGE ***:  '.$exception->getMessage().',  *** TRACE ***: '.$exception->getTraceAsString());
            $this->logger->error('Failed to prepare the quote data. *** MESSAGE ***:  '.$exception->getMessage());

            return;
        }

        $this->logger->debug('Quote data: ' . json_encode($requestData));

        // Try/catch because we don't want to disturb critical processes such as the checkout
        try {
            $this->client->sendData($requestData, InterconnectClient::ENDPOINT_QUOTE);
        }catch(Throwable $exception) {
//            $this->logger->error('Failed to send quote. *** MESSAGE ***: '.$exception->getMessage().',  *** TRACE ***:'.$exception->getTraceAsString());
            $this->logger->error('Failed to send quote. *** MESSAGE ***: '.$exception->getMessage());

            return;
        }

        $this->logger->info(__METHOD__.' :: Quote sent to Interconnect ('.$quote->getId().')');
    }
}