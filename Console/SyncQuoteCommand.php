<?php
namespace Gracious\Interconnect\Console;

use Magento\Framework\App\State;
use Magento\Quote\Model\QuoteRepository;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Http\Request\Client;
use Magento\Catalog\Helper\Image as ImageHelper;
use Symfony\Component\Console\Input\InputOption;
use Gracious\Interconnect\Console\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gracious\Interconnect\Http\Request\Data\Quote\Factory as QuoteDataFactory;

/**
 * Class SyncQuoteCommand
 * @package Gracious\Interconnect\Console
 */
class SyncQuoteCommand extends CommandAbstract
{
    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * SyncQuote constructor.
     * @param State $state
     * @param Logger $logger
     * @param Client $client
     * @param Config $config
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(State $state, Logger $logger, Client $client, Config $config, QuoteRepository $quoteRepository)
    {
        parent::__construct($state, $logger, $client, $config);

        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
     * @todo Validate --id option as integer
     */
    protected function configure()
    {
        $this->setName('interconnect:syncquote')->setDescription('Send a quote to the Interconnect webservice');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'quoteId', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->config->isComplete()) {
            $this->logger->error(__METHOD__.' :: Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $quoteId = $input->getOption('id');
        $this->evalInt($quoteId);
        $quote = $this->quoteRepository->get($quoteId);

        if($quote === null) {
            $output->writeln('No quote found, aborting...');
        }

        $output->writeln('Found quote, sending...');

        $quoteDataFactory = new QuoteDataFactory();
        $requestData = $quoteDataFactory->setupData($quote);
        $this->client->sendData($requestData, Client::ENDPOINT_QUOTE);
    }
}