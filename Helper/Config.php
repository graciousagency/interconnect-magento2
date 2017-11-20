<?php

namespace Gracious\Interconnect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Gracious\Interconnect\Helper
 */
class Config extends AbstractHelper
{

    const XML_PATH_SERVICE_BASE_URL = 'gracious_interconnect/settings/baseurl';
    const XML_PATH_PREFIX = 'gracious_interconnect/settings/prefix';
    const XML_PATH_API_KEY = 'gracious_interconnect/settings/apikey';

    /**
     * @var string[]
     */
    protected $lazyData = [];

    /**
     * Returns the base url for the webservice from the application's main config
     * @return string
     */
    public function getInterconnectServiceBaseUrl()
    {
        return $this->getLazy(static::XML_PATH_SERVICE_BASE_URL);
    }

    /**
     * Returns the base url for the webservice from the application's main config
     * @return string
     */
    public function getInterconnectPrefix()
    {
        return $this->getLazy(static::XML_PATH_PREFIX);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->getLazy(static::XML_PATH_API_KEY);
    }

    /**
     * Lazy loading
     * @param string $xmlPath
     * @return string
     */
    protected function getLazy($xmlPath)
    {
        if (!isset($this->lazyData[$xmlPath])) {
            $this->lazyData[$xmlPath] = $this->scopeConfig->getValue($xmlPath, ScopeInterface::SCOPE_STORE);
        }

        return $this->lazyData[$xmlPath];
    }

    /**
     * @return bool
     * Returns whether the required config values are set
     */
    public function isComplete()
    {
        $serviceBaseUrl = trim($this->getInterconnectServiceBaseUrl());
        $interconnectPrefix = trim($this->getInterconnectPrefix());
        $apiKey = trim($this->getApiKey());

        return is_string($serviceBaseUrl) &&
               is_string($interconnectPrefix) &&
               is_string($apiKey);
    }
}