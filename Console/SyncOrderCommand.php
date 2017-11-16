<?php
namespace Gracious\Interconnect\Console;

use Throwable;
use Magento\Sales\Model\Order;
use Magento\Framework\App\State;
use Magento\Sales\Model\OrderRepository;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Http\Request\Client;
use Symfony\Component\Console\Input\InputOption;
use Gracious\Interconnect\Console\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order as OrderDataFactory;


class SyncOrderCommand extends CommandAbstract
{

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * SyncOrderCommand constructor.
     * @param State $state
     * @param Logger $logger
     * @param Client $client
     * @param Config $config
     * @param OrderRepository $orderRepository
     */
    public function __construct(State $state, Logger $logger,  Client $client, Config $config, OrderRepository $orderRepository)
    {
        parent::__construct($state, $logger, $client, $config);

        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('interconnect:syncorder')->setDescription('Send an order to the Interconnect webservice');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'orderId', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->config->isComplete()) {
            $output->writeln('Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $orderId = $input->getOption('id');
        $this->evalInt($orderId);
        /* @var $order Order */ $order = $this->orderRepository->get($orderId);

        if($order === null) {
            $output->writeln('No order found, aborting....');

            return;
        }

        $output->write('Found order ('.$orderId.'), sending...');
        $orderDataFactory = new OrderDataFactory();
        $requestData = $orderDataFactory->setupData($order);
        $this->client->sendData($requestData, InterconnectClient::ENDPOINT_ORDER);
    }
}