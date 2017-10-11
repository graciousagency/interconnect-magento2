<?php

namespace Gracious\Interconnect\Console;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Monolog\Handler\HandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Psr\Log\LoggerInterface as Logger;

class SyncCustomer extends Command
{


    private $logger;
    /**
     * @var CustomerRepositoryInterface
     */
    private $repository;


    public function __construct(State $state, Logger $logger, CustomerRepositoryInterface $repository)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->repository = $repository;
    }

    protected function configure()
    {
        $this->setName('interconnect:synccustomer')->setDescription('Sync a customer with copernica');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'customerId', null);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $model = $this->repository->getById($input->getOption('id'));

        $requestData = $this->getRequestData($model);

        $this->logger->debug(json_encode($requestData));

        return;
        $client = new Client('http://localhost/customer');
        $response = $client->setMethod(Request::METHOD_POST)->setHeaders(['Content-Type' => 'application/json'])->setRawBody(json_encode($requestData))->getResponse();

        print_r($response->getBody());


    }

    /**
     * @param $model \Magento\Customer\Model\Data\Customer
     * @return array
     */
    protected function getRequestData($model)
    {
        // emailAddress, string firstName, string lastName, string birthDate, string gender, bool subscribe
        $addresses = $model->getAddresses();


        return [

            'emailAddress' => $model->getEmail(),
            'firstName' => $model->getFirstname(),
            'lastName' => $model->getLastname(),
            'birthDate' => $model->getDob(),
            'gender' => $model->getGender(),
            'subscribe' => true,

        ];
    }


}