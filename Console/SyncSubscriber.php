<?php

namespace Gracious\Interconnect\Console;

use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\EventManager\EventManager;
use Zend\Http\Client;
use Zend\Http\Request;
use Psr\Log\LoggerInterface as Logger;

class SyncSubscriber extends Command
{


    /**
     * @var Customer
     */
    private $entity;
    /**
     * @var State
     */
    private $state;

    private $logger;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    private $collection;


    public function __construct(State $state, Logger $logger, Subscriber $entity)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->entity = $entity;
        $this->state = $state;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->eventManager = $objectManager->get(ManagerInterface::class);

        $this->collection = $objectManager->get(\Magento\Newsletter\Model\ResourceModel\Subscriber::class );

        try{
            $state->setAreaCode('adminhtml');
        }
        catch(\Exception $e){

        }

    }

    protected function configure()
    {
        $this->setName('interconnect:syncsubscriber')->setDescription('Sync a subscriber with copernica');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'subscriberId', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $fac = \Magento\Framework\App\ObjectManager::getInstance()->create(SubscriberFactory::class);
        $fac->create()->subscribe('dikkepoeper@gracious.nl');

   /*     $subscriber = $this->entity->loadByEmail($input->getOption('id'));
        if($subscriber){


            $this->eventManager->dispatch('newsletter_subscriber_save_after',['subscriber'=>$subscriber]);

            $client = new Client('http://localhost/subscribe/popup');

            $response = $client->setMethod(Request::METHOD_POST)
                ->setHeaders(['Content-Type'=>'application/json'])
                ->setRawBody(json_encode(['subscriptionId'=> $subscriber['subscriber_id'],'emailAddress'=>$subscriber['subscriber_email']]))
                ->send();


            print_r($response->getStatusCode());
        }
        return;*/


    }

}