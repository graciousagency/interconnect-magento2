<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Exception;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Support\Formatter;
use Magento\Framework\App\ObjectManager;

/**
 * Class FactoryAbstract
 * @package Gracious\Interconnect\Http\Request\Data
 */
abstract class FactoryAbstract
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * FactoryAbstract constructor.
     */
    public function __construct()
    {
        $this->logger = ObjectManager::getInstance()->create(Logger::class);
        $this->config = ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @param int|string $id
     * @param string $entityPrefix
     * @return string
     * @throws Exception
     * The exception throwing is necessary because the id must be unique and thus complete. We really want to let
     * the calling class know when that fails.
     */
    protected final function generateEntityId($id, $entityPrefix)
    {
        if ($id === null || trim($id) == '') {
            throw new Exception('Unable to format prefixed ID: invalid entity id!');
        }

        if (!is_string($entityPrefix) || trim($entityPrefix) == '') {
            throw new Exception('Unable to format prefixed ID: invalid entity prefix!');
        }

        $merchantHandle = $this->config->getInterconnectPrefix();

        if (!is_string($merchantHandle) || trim($merchantHandle) == '') {
            throw new Exception('Unable to format prefixed ID: Merchant handle not set!');
        }

        return Formatter::prefixID($id, $entityPrefix, $this->config->getInterconnectPrefix());
    }
}