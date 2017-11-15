<?php

namespace Gracious\Interconnect\Foundation;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Environment
{

    /**
     * @var static
     */
    private static $instance;

    /**
     * @var string
     */
    protected $moduleVersion;

    /**
     * @var string
     */
    protected $moduleType;

    /**
     * @var string
     */
    protected $magentoVersion;

    /**
     * @var string
     */
    protected $domain;

    /**
     * Gracious_Interconnect_Foundation_Environment constructor.
     */
    private function __construct()
    {
        $this->moduleVersion = static::parseModuleVersion();
        $this->moduleType = static::parseModuleType();
        $objectManager = ObjectManager::getInstance();

        $productMetadata = $objectManager->get(ProductMetadataInterface::class);
        $this->magentoVersion = $productMetadata->getVersion();

        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $domain = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = rtrim($domain, '/');
        $this->domain = $domain;
    }

    /**
     * @return null|string
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * @return string
     */
    public function getModuleType()
    {
        return $this->moduleType;
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion;
    }

    /**
     * @return mixed|string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'moduleVersion' => $this->moduleVersion,
            'moduleType' => $this->moduleType,
            'magentoVersion' => $this->magentoVersion,
            'domain' => $this->domain
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return json_encode($this->toArray());
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return null|string
     */
    public static function parseModuleVersion()
    {
        return static::parseComposerValue('version');
    }

    /**
     * @return string
     */
    public static function parseModuleType()
    {
        return static::parseComposerValue('type');
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    protected static function parseComposerValue($key, $default = null)
    {
        $composerFileHandle = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'composer.json';

        if (!file_exists($composerFileHandle) || !is_readable($composerFileHandle)) {
            return $default;
        }

        $data = json_decode(file_get_contents($composerFileHandle));

        if (!isset($data->{$key})) {
            return $default;
        }

        return $data->{$key};
    }

    /**
     * @return bool
     */
    public static function isInDeveloperMode()
    {
        $state = ObjectManager::getInstance()->get(State::class);

        return State::MODE_DEVELOPER === $state->getMode();
    }
}