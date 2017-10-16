<?php
namespace Gracious\Interconnect\Http\Request;

use Exception;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Client as Base;
use Gracious\Interconnect\Helper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Helper\Config;

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
        $this->baseUrl = $baseUrl;

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
     * @return bool
     * @throws Exception
     */
    public function sendData(array $data, $endPoint) {
        if($this->baseUrl === null){
            throw new Exception('Unable to make request: base url not set');
        }

        $this->setMethod(Request::METHOD_POST)
            ->setUri($this->baseUrl.'/'.$endPoint)
            ->setHeaders(['Content-Type' => 'application/json'])
            ->setRawBody(json_encode($data))
        ;
        $this->logger->debug(__METHOD__.':: Posting to \''.$this->baseUrl.'/'.$endPoint.'\'...');
        $response = $this->send();

        $this->processResponse($response);
    }

    /**
     * @param Response $response
     */
    protected function processResponse(Response $response) {
        $request = $this->getRequest();
        $statusCode = $response->getStatusCode();
        $success = ($statusCode == 200);

        if(!$success) {
            $this->logger->error('Response status = '.$statusCode.', response = '.(string)$response);
            
            throw new Exception('Error making request to \''.$request->getUriString().'\' with http status code :'.$statusCode.' and response '.(string)$response);
        }
    }
}