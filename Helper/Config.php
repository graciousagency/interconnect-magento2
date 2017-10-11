<?php
namespace Gracious\Interconnect\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 * @package Gracious\Interconnect\Helper
 */
class Config extends AbstractHelper
{

    const XML_PATH_SERVICE_BASE_URL     = 'gracious_interconnect/settings/baseurl';
    const XML_PATH_PREFIX               = 'gracious_interconnect/settings/prefix';

    /**
     * Returns the base url for the webservice from the application's main config
     * @return string
     */
    public function getInterconnectServiceBaseUrl() {
        return $this->scopeConfig->getValue(self::XML_PATH_SERVICE_BASE_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns the base url for the webservice from the application's main config
     * @return string
     */
    public function getInterconnectPrefix() {
        return $this->scopeConfig->getValue(self::XML_PATH_PREFIX, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     * Returns whether the required config values are set
     */
    public function isComplete() {
        $serviceBaseUrl = $this->getInterconnectServiceBaseUrl();
        $interconnectPrefix = $this->getInterconnectPrefix();

        return (is_string($serviceBaseUrl) && trim($serviceBaseUrl) != '') &&
            (is_string($interconnectPrefix) && trim($interconnectPrefix) != '');
    }
}