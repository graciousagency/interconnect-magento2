<?php

namespace Gracious\Interconnect\Api;

use Gracious\Interconnect\Helper\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class Copernica
 * @package Gracious\Interconnect\Api
 * @api
 */
class Copernica implements CopernicaInterface
{
    /**
     * @var Config
     */
    private $helperConfig;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @param Config $config
     * @param Http $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(Config $config, Http $request, CustomerRepositoryInterface $customerRepository, SubscriberFactory $subscriberFactory)
    {
        $this->helperConfig = $config;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @return void
     */
    public function updateProfile()
    {
        if (!$this->usedCorrectSecret()) {
            throw new \RuntimeException('Invalid or empty X-Secret sent');
        }

        $data = json_decode($this->request->getContent(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(json_last_error_msg());
        }

        if ('update' !== $data['action']) {
            throw new \RuntimeException('Only updates are accepted');
        }

        $subscriber = $this->subscriberFactory->create()->loadByEmail($data['fields']['email']);
        if ((empty($data['fields']['newsletter']) || 'unsubscribed' === $data['fields']['newsletter']) && ($subscriber->getId() && Subscriber::STATUS_SUBSCRIBED == $subscriber->getSubscriberStatus())) {
            $subscriber->unsubscribe($data['fields']['email']);
        }

        if (!empty($data['fields']['newsletter']) && 'subscribed' === $data['fields']['newsletter'] && Subscriber::STATUS_SUBSCRIBED !== $subscriber->getSubscriberStatus()) {
            $subscriber->subscribe($data['fields']['email']);
        }
    }

    /**
     * @return bool
     */
    private function usedCorrectSecret()
    {
        return ($this->helperConfig->getApiKey() == $this->request->getHeader('X-Secret'));
    }
}
