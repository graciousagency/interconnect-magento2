<?php
namespace Gracious\Interconnect\Console;

use Magento\Framework\App\State;
use Psr\Log\LoggerInterface as Logger;
use Magento\Quote\Model\QuoteRepository;
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
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(State $state, Logger $logger, Client $client, QuoteRepository $quoteRepository)
    {
        parent::__construct($state, $logger, $client);

        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritdoc}
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
        $quote = $this->quoteRepository->get($input->getOption('id'));

        if($quote === null) {
            $output->writeln('No quote found, aborting...');
        }

        $output->writeln('Found quote, sending...');

        $quoteDataFactory = new QuoteDataFactory();
        $requestData = $quoteDataFactory->setupData($quote);
        $this->client->sendData($requestData, Client::ENDPOINT_QUOTE);
    }
}