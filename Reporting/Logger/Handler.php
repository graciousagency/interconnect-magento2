<?php
namespace Gracious\Interconnect\Reporting\Logger;

use \Monolog\Logger;
use \Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 * @package Gracious\Interconnect\Reporting\Logger
 * Custom logger handler so we have our own log for this module.
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/interconnect.log';
}