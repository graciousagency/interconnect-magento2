<?php

namespace Gracious\Interconnect\Magento\Newsletter\Controller\Subscriber;

use Exception;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Subscriber\Factory as SubscriberFactory;
use Gracious\Interconnect\Reporting\Logger;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Controller\Subscriber\Confirm as Base;
use Magento\Newsletter\Model\Subscriber;

class Confirm extends Base
{
    /**
     * Subscription confirm action
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $code = (string)$this->getRequest()->getParam('code');

        if ($id && $code) {
            /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
            $subscriber = $this->_subscriberFactory->create()->load($id);

            if ($subscriber->getId() && $subscriber->getCode()) {
                if ($subscriber->confirm($code)) {
                    $this->messageManager->addSuccessMessage(__('Your subscription has been confirmed.'));
                    $this->sendSubscription($subscriber->getEmail());
                } else {
                    $this->messageManager->addErrorMessage(__('This is an invalid subscription confirmation code.'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('This is an invalid subscription ID.'));
            }
        }

        $this->getResponse()->setRedirect($this->_storeManager->getStore()->getBaseUrl());
    }

    /**
     * @param string $emailAddress
     */
    protected function sendSubscription($emailAddress)
    {
        /* @var $subscriber Subscriber */
        $subscriber = ObjectManager::getInstance()->create(Subscriber::class)->loadByEmail($emailAddress);

        if ($subscriber !== null && $subscriber->getEmail() == $emailAddress) {
            /* @var $client InterconnectClient */
            $client = ObjectManager::getInstance()->create(InterconnectClient::class);
            /* @var $logger Logger */
            $logger = ObjectManager::getInstance()->create(Logger::class);
            $subscriberFactory = new SubscriberFactory();

            try {
                $requestData = $subscriberFactory->setupData($subscriber);
                $client->sendData($requestData, InterconnectClient::ENDPOINT_NEWSLETTER_SUBSCRIBER);
            } catch (Exception $exception) {
                $logger->exception($exception);

                return;
            }
        }
    }
}