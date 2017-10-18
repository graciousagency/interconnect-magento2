<?php
namespace Gracious\Interconnect\Observer;

use Exception;
use Magento\Catalog\Helper\Image;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Http\Request\Client;
use Magento\Framework\Event\ObserverInterface;

abstract class ObserverAbstract implements ObserverInterface
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
     * @throws Exception;
     */
    public function generateEntityId($id, $entityPrefix) {
        if($id === null || trim($id) == '') {
            // Throw an exception because formatting a unique handle is a critical step
            throw new Exception('Unable to format prefixed ID: invalid entity id!');
        }

        if(!is_string($entityPrefix) || trim($entityPrefix) == '') {
            // Throw an exception because formatting a unique handle is a critical step
            throw new Exception('Unable to format prefixed ID: invalid entity prefix!');
        }

        $merchantHandle = $this->config->getInterconnectPrefix();

        if(!is_string($merchantHandle) || trim($merchantHandle) == '') {
            // Throw an exception because formatting a unique handle is a critical step
            throw new Exception('Unable to format prefixed ID: Merchant handle not set!');
        }

        return Formatter::prefixID($id, $entityPrefix, $this->config->getInterconnectPrefix());
    }
}