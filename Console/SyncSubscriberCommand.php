<?php
namespace Gracious\Interconnect\Console;

use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Gracious\Interconnect\Http\Request\Client;
use Symfony\Component\Console\Input\InputOption;
use Gracious\Interconnect\Console\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gracious\Interconnect\Http\Request\Data\Subscriber\Factory as SubscriberFactory;

/**
 * Class SyncSubscriberCommand
 * @package Gracious\Interconnect\Console
 */
class SyncSubscriberCommand extends CommandAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('interconnect:syncsubscriber')->setDescription('Send a subscriber to the Interconnect webservice');
        $this->addOption('id',null, InputOption::VALUE_REQUIRED, 'subscriberId', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $utilitySubscriber Subscriber */ $utilitySubscriber = ObjectManager::getInstance()->create(Subscriber::class);
        $subscriber = $utilitySubscriber->loadByCustomerId($input->getOption('id'));

        if($subscriber === null) {
            $output->write('Subscriber not found, all done here ....');

            return;
        }

        $output->write('Found subscriber, sending...');

        $subscriberFactory = new SubscriberFactory();
        $requestData = $subscriberFactory->setupData($subscriber);
        $this->client->sendData($requestData, Client::ENDPOINT_NEWSLETTER_SUBSCRIBER);
    }
}