<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Http\Request\Client;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\System\Exception as InterconnectException;
use Gracious\Interconnect\System\InvalidArgumentException;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Event\ObserverInterface;

abstract class Observer implements ObserverInterface
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Image
     */
    protected $imageHelper;


    /**
     * ObserverAbstract constructor.
     * @param Logger $logger
     * @param Config $config
     * @param Client $client
     * @param Image $imageHelper
     */
    public function __construct(Logger $logger, Config $config, Client $client, Image $imageHelper)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->client = $client;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param int|string $id
     * @param string $entityPrefix
     * @return string
     * @throws InterconnectException;
     * @throws InvalidArgumentException
     */
    public function generateEntityId($id, $entityPrefix)
    {
        if (null === $id || '' == trim($id)) {
            throw new InvalidArgumentException('Unable to format prefixed ID: invalid entity id!');
        }

        if (!is_string($entityPrefix) || '' == trim($entityPrefix)) {
            throw new InvalidArgumentException('Unable to format prefixed ID: invalid entity prefix!');
        }

        $merchantHandle = $this->config->getInterconnectPrefix();

        if (!is_string($merchantHandle) || '' == trim($merchantHandle)) {
            throw new InterconnectException('Unable to format prefixed ID: Merchant handle not set!');
        }

        return Formatter::prefixID($id, $entityPrefix, $this->config->getInterconnectPrefix());
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

    }
}