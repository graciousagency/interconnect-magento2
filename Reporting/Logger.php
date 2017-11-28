<?php
namespace Gracious\Interconnect\Reporting;

use Exception;
use Monolog\Logger as Base;
use Throwable;

/**
 * Class Logger
 * @package Gracious\Interconnect\Reporting
 * Custom logger (yes this class is empty but that's the way it works with Magento so don't delete it)
 */
class Logger extends Base
{
    /**
     * @param Throwable $exception
     */
    public function exception(Throwable $exception) {
        $this->alert('*** EXCEPTION ' . str_repeat('*****', 20));
        $this->alert('*** Type: ' . get_class($exception));
        $this->alert('*** File: ' . $exception->getFile());
        $this->alert('*** Line: ' . $exception->getLine());
        $this->alert('*** Message: ' . $exception->getMessage());
        $this->alert('*** Trace:  ' . $exception->getTraceAsString());
    }
}