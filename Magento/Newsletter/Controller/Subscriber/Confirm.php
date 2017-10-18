<?php
namespace Gracious\Interconnect\Magento\Newsletter\Controller\Subscriber;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Gracious\Interconnect\Reporting\Logger;
use Magento\Newsletter\Controller\Subscriber\Confirm as Base;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Subscriber\Factory as SubscriberFactory;

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
                    $this->messageManager->addSuccess(__('Your subscription has been confirmed.'));
                    $this->sendSubscription($subscriber->getEmail());
                } else {
                    $this->messageManager->addError(__('This is an invalid subscription confirmation code.'));
                }
            } else {
                $this->messageManager->addError(__('This is an invalid subscription ID.'));
            }
        }

        $this->getResponse()->setRedirect($this->_storeManager->getStore()->getBaseUrl());
    }

    /**
     * @param string $emailAddress
     */
    protected function sendSubscription($emailAddress) {
        /* @var $subscriber Subscriber */ $subscriber = ObjectManager::getInstance()->create(Subscriber::class)->loadByEmail($emailAddress);

        if($subscriber !== null && $subscriber->getEmail() == $emailAddress) {
            /* @var $client InterconnectClient */ $client = ObjectManager::getInstance()->create(InterconnectClient::class);
            /* @var $logger Logger */ $logger = ObjectManager::getInstance()->create(Logger::class);
            $subscriberFactory = new SubscriberFactory();

            try {
                $requestData = $subscriberFactory->setupData($subscriber);
                $client->sendData($requestData, InterconnectClient::ENDPOINT_NEWSLETTER_SUBSCRIBER);
            }catch (Exception $exception) {
                $logger->error(__METHOD__.' :: Exception while sending subscription ('.$subscriber->getId().'), MESSAGE = '.$exception->getMessage().', TRACE = '.$exception->getTraceAsString());

                return;
            }

            $logger->info(__METHOD__.' :: Sent subscriber ('.$subscriber->getId().') to Interconnect');
        }
    }
}