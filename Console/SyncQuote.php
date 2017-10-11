<?php

namespace Gracious\Interconnect\Console;

use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Psr\Log\LoggerInterface as Logger;

class SyncQuote extends Command
{


    /**
     * @var Collection
     */
    private $collection;
    /**
     * @var Customer
     */
    private $entity;


    public function __construct(State $state, Collection $collection, Quote $entity)
    {
        parent::__construct();

        $this->collection = $collection;
        $this->entity = $entity;
    }

    protected function configure()
    {
        $this->setName('interconnect:syncquote')->setDescription('Sync a quote with copernica');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'quoteId', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $client = new Client('http://localhost/subscribe/popup');
        $response = $client->setMethod(Request::METHOD_POST)->setHeaders(['Content-Type'=>'application/json'])->setRawBody(json_encode(['subscriptionId'=> 1,'emailAddress'=>'bob@graciousstudios.nl']))->getResponse();

        print_r($response->getBody());


    }

}