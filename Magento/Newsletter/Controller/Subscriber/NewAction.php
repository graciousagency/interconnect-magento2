<?php
namespace Gracious\Interconnect\Magento\Newsletter\Controller\Subscriber;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Gracious\Interconnect\Reporting\Logger;
use Magento\Newsletter\Controller\Subscriber\NewAction as Base;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Subscriber\Factory as SubscriberFactory;

class NewAction extends Base
{

    /**
     * New subscription action
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $email = (string)$this->getRequest()->getPost('email');

            try {
                $this->validateEmailFormat($email);
                $this->validateGuestSubscription();
                $this->validateEmailAvailable($email);

                $status = $this->_subscriberFactory->create()->subscribe($email);
                if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE) {
                    $this->messageManager->addSuccess(__('The confirmation request has been sent.'));
                } else {
                    $this->sendSubscription($email);
                    $this->messageManager->addSuccess(__('Thank you for your subscription.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addException(
                    $e,
                    __('There was a problem with the subscription: %1', $e->getMessage())
                );
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong with the subscription.'));
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
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
                $logger->exception($exception);

                return;
            }

            $logger->info(__METHOD__.' :: Sent subscriber ('.$subscriber->getId().') to Interconnect');
        }
    }
}