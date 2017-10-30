<?php
namespace Gracious\Interconnect\Http\Request;

use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Client as Base;
use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
use Gracious\Interconnect\Foundation\Environment;
use Zend\Http\Client\Adapter\Curl as CurlAdapter;
use Gracious\Interconnect\System\Exception as InterconnectException;

class Client extends Base
{
    const ENDPOINT_CUSTOMER                             = 'customer/register';
    const ENDPOINT_NEWSLETTER_SUBSCRIBER                = 'newsletter/subscribe/popup';
    const ENDPOINT_ORDER                                = 'order/process';
    const ENDPOINT_QUOTE                                = 'quote/process';

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Client constructor.
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(Config $config, Logger $logger)
    {
        $this->helperConfig = $config;
        $this->logger = $logger;
        $this->setBaseUrl($config->getInterconnectServiceBaseUrl());

        parent::__construct(null, null );
    }

    /**
     * @param string $baseUrl
     * @return Client
     */
    public function setBaseUrl($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
     * @param array $data
     * @param string $endPoint
     * @throws InterconnectException
     * @throws \Zend\Http\Client\Exception\RuntimeException
     */
    public function sendData(array $data, $endPoint) {
        if($this->baseUrl === null){
            throw new InterconnectException('Unable to make request: base url not set');
        }

        if(Environment::isInDeveloperMode()) {
            // Overcome ssl problems on local machine
            $this->logger->notice(__METHOD__.'=> Local machine; disabling ssl checks...');
            $curlAdapter = new CurlAdapter();
            $curlAdapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
            $curlAdapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            $this->setAdapter($curlAdapter);
        }

        $metaData = Environment::getInstance();
        $json = json_encode($data);

        if(Environment::isInDeveloperMode()) {
            $this->logger->info(str_repeat('*****', 30));
            $this->logger->info(__METHOD__.':: Posting to \''.$this->baseUrl.'/'.$endPoint.'\'. Data = '.$json);
        }

        $this->setMethod(Request::METHOD_POST)
            ->setUri($this->baseUrl.'/'.$endPoint)
            ->setHeaders([
                'Content-Type'      => 'application/json',
                'X-Secret'          => $this->helperConfig->getApiKey(),
                'X-ModuleType'      => $metaData->getModuleType(),
                'X-ModuleVersion'   => $metaData->getModuleVersion(),
                'X-AppHandle'       => 'magento2',
                'X-AppVersion'      => $metaData->getMagentoVersion(),
                'X-Domain'          => $metaData->getDomain()
            ])
            ->setRawBody($json)
        ;

        $response = $this->send();

        $this->processResponse($response);
    }

    /**
     * @param Response $response
     * @throws InterconnectException
     */
    protected function processResponse(Response $response) {
        $request = $this->getRequest();
        $statusCode = $response->getStatusCode();
        $success = ($statusCode == 200);

        if(!$success) {
            $this->logger->error('Response status = '.$statusCode.', response = '.(string)$response);
            
            throw new InterconnectException('Error making request to \''.$request->getUriString().'\' with http status code :'.$statusCode.' and response '.(string)$response);
        }

        if(Environment::isInDeveloperMode()) {
            $this->logger->info('Data sent to: '.$request->getUriString().'. All done here...');
        }
    }
}