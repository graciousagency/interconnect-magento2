<?php
namespace Gracious\Interconnect\Console;

use Exception;
use Magento\Framework\App\State;
use Monolog\Handler\HandlerInterface;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Http\Request\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Magento\Customer\Model\ResourceModel\Customer;
use Gracious\Interconnect\Support\Validation\RegEx;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Customer\Model\ResourceModel\Customer\Collection;

/**
 * Class CommandAbstract
 * @package Gracious\Interconnect\Console
 */
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
     * Config
     */
    protected $config;

    /**
     * CommandAbstract constructor.
     * @param State $state
     * @param Logger $logger
     */
    public function __construct(State $state, Logger $logger, Client $client, Config $config)
    {
        $this->setAreaCode($state);
        
        parent::__construct();

        $this->logger = $logger;
        $this->client = $client;
        $this->config = $config;
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

    /**
     * @param mixed $value
     * @throws Exception
     */
    protected function evalInt($value) {
        // Cast to string if it's a numeric type because regex evaluates strings
        $value = is_numeric($value) ? (string)$value : $value;

        if(!is_string($value) || !RegEx::test(RegEx::INT, $value)) {
            throw new Exception('Expected integer but got '.gettype($value));
        }
    }
}