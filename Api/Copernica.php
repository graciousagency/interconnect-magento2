<?php

namespace Gracious\Interconnect\Api;

use Gracious\Interconnect\Helper\Config;
use Gracious\Interconnect\Reporting\Logger;
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
     * @var Logger
     */
    private $logger;

    /**
     * @param Config $config
     * @param Http $request
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriberFactory $subscriberFactory
     */
    public function __construct(Config $config, Http $request, CustomerRepositoryInterface $customerRepository, SubscriberFactory $subscriberFactory, Logger $logger)
    {
        $this->helperConfig = $config;
        $this->request = $request;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function updateProfile(): bool
    {
        try {
            $data = '';
            if (!$this->usedCorrectSecret()) {
                throw new \RuntimeException('Invalid or empty X-Secret sent');
            }

            $data = json_decode($this->request->getContent(), true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException(json_last_error_msg());
            }

            if (!is_array($data)) {
                $this->logger->info('$data is not an array! - ' . $data);
            }

            if ('update' !== $data['action']) {
                $this->logger->info('Callback was not an update, ignoring it');
                throw new \RuntimeException('Only updates are accepted');
            }

            $email = $data['profile']['fields']['email'];
            $subscriber = $this->subscriberFactory->create()->loadByEmail($email);
            if ((empty($data['parameters']['newsletter']) || 'unsubscribed' === $data['parameters']['newsletter']) && ($subscriber->getId() && Subscriber::STATUS_SUBSCRIBED == $subscriber->getSubscriberStatus())) {
                $this->logger->info('Unsubscribing ' . $email);
                $subscriber->unsubscribe($email);
                return true;
            }

            if (!empty($data['parameters']['newsletter']) && 'subscribed' === $data['parameters']['newsletter'] && Subscriber::STATUS_SUBSCRIBED !== $subscriber->getSubscriberStatus()) {
                $this->logger->info('Subscribing ' . $email);
                $subscriber->subscribe($email);
                return true;
            }

            $this->logger->info('Nothing to do on update, update was not a subscribe or unsubscribe update');

        } catch (\Throwable $e) {
            $this->logger->exception($e);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function usedCorrectSecret()
    {
        return ($this->helperConfig->getApiKey() == $this->request->getHeader('X-Secret'));
    }
}
