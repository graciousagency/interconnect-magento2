<?php

namespace Gracious\Interconnect\Console;

use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Http\Request\Client;
use Gracious\Interconnect\Http\Request\Data\Subscriber as SubscriberFactory;
use Gracious\Interconnect\Reporting\Logger;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Newsletter\Model\Subscriber;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SyncSubscriberCommand
 * @package Gracious\Interconnect\Console
 */
class SyncSubscriberCommand extends CommandAbstract
{
    public function __construct(State $state, Logger $logger, Client $client, Config $config)
    {
        parent::__construct($state, $logger, $client, $config);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('interconnect:syncsubscriber')->setDescription('Send a subscriber to the Interconnect webservice');
        $this->addOption('id', null, InputOption::VALUE_REQUIRED, 'subscriberId', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->config->isComplete()) {
            $output->writeln('Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $subscriberId = $input->getOption('id');
        $this->evalInt($subscriberId);
        $objectManager = ObjectManager::getInstance();
        /* @var $subscriber Subscriber */
        $subscriber = $objectManager->create(Subscriber::class)->load($subscriberId);

        if ($subscriber === null || $subscriber->getId() !== $subscriberId) {
            $output->write('Subscriber not found, all done here ....');

            return;
        }

        $output->write('Found subscriber \'' . $subscriber->getEmail() . '\', sending...');
        $subscriberFactory = new SubscriberFactory();
        $requestData = $subscriberFactory->setupData($subscriber);
        $this->client->sendData($requestData, Client::ENDPOINT_NEWSLETTER_SUBSCRIBER);
    }
}