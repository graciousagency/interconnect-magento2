<?php
namespace Gracious\Interconnect\Reporting\Logger;

use \Monolog\Logger;
use \Magento\Framework\Logger\Handler\Base;

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