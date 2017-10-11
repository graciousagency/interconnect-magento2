<?php
namespace Gracious\Interconnect\Console;

use Exception;
use Magento\Framework\App\State;
use Monolog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface as Logger;
use Gracious\Interconnect\Http\Request\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Customer\Model\ResourceModel\Customer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;

abstract class CommandAbstract extends Command
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Client
     */
    protected $client;

    /**
     * CommandAbstract constructor.
     * @param State $state
     * @param Logger $logger
     */
    public function __construct(State $state, Logger $logger, Client $client)
    {
        $this->setAreaCode($state);
        
        parent::__construct();

        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @param State $state
     */
    protected function setAreaCode(State $state) {
        try {
            $state->getAreaCode();
        }catch (Exception $exception) {
            $state->setAreaCode('adminhtml');
        }
    }
}