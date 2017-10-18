<?php
namespace Gracious\Interconnect\Console;

use Magento\Sales\Model\Order;
use Magento\Framework\App\State;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Http\Request\Client;
use Magento\Catalog\Helper\Image as ImageHelper;
use Symfony\Component\Console\Input\InputOption;
use Gracious\Interconnect\Reporting\OutputAdapter;
use Gracious\Interconnect\Console\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gracious\Interconnect\Generic\Behaviours\SendsOrder;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;


class SyncOrderCommand extends CommandAbstract
{
    use SendsOrder;

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
     * @todo Validate --id option as integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->config->isComplete()) {
            $this->logger->error(__METHOD__.' :: Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        /* @var $order Order */ $order = $this->orderRepository->get($input->getOption('id'));

        if($order === null) {
            $output->writeln('No order found, aborting....');
        }

        $this->sendOrder($order, new OutputAdapter($output), $this->client);
    }
}