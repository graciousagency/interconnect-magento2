<?php
namespace Gracious\Interconnect\Console;

use Magento\Framework\App\State;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Http\Request\Client;
use Symfony\Component\Console\Input\InputOption;
use Gracious\Interconnect\Console\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Gracious\Interconnect\Http\Request\Data\Customer\Factory as CustomerDataFactory;

/**
 * Class SyncCustomerCommand
 * @package Gracious\Interconnect\Console
 */
class SyncCustomerCommand extends CommandAbstract
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * SyncCustomer constructor.
     * @param State $state
     * @param Logger $logger
     * @param Config $config
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(State $state, Logger $logger,  Client $client, Config $config, CustomerRepositoryInterface $customerRepository)
    {
        parent::__construct($state, $logger, $client, $config);

        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('interconnect:synccustomer')->setDescription('Send a customer to the Interconnect webservice');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'customerId', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!$this->config->isComplete()) {
            $output->write('Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $customerId = $input->getOption('id');
        $this->evalInt($customerId);
        $customer = $this->customerRepository->getById($customerId);

        if($customer === null) {
            $output->write('No customer found, aborting....');

            return;
        }

        $output->write('Found customer('.$customerId.'), sending...');
        $customerDataFactory = new CustomerDataFactory();
        $requestData = $customerDataFactory->setupData($customer);
        $this->client->sendData($requestData, Client::ENDPOINT_CUSTOMER);
    }
}