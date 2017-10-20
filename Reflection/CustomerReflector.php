<?php
namespace Gracious\Interconnect\Reflection;

use Exception;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Gracious\Interconnect\Model\CustomerHistoricInfo;

/**
 * Class CustomerInspector
 * @package Gracious\Interconnect\Helper
 * Provides extra data about a customer that can't be derived from a customer model or other object directly
 */
class CustomerReflector extends  AbstractHelper
{

    /**
     * @param $email
     * @return CustomerHistoricInfo
     */
    public function getCustomerHistoricInfoByCustomerEmail($email) {
        /* @var $customerRepository CustomerRepositoryInterface  */ $customerRepository = ObjectManager::getInstance()->create(CustomerRepositoryInterface::class);

        try {
            /* @var $customer CustomerInterface */ $customer = $customerRepository->get($email);
        }catch (Exception $exception) {
            // pfff, Magento throws an exception if it can't find the customer instead of just returning null
            $customer = null;
        }

        $orders = $this->getCustomerOrdersByCustomerEmail($email);
        $totalOrderCount = count($orders);
        $totalOrderAmount = 0.00;
        $firstOrderDate = null;
        $lastOrderDate = null;
        $index = 0;
        $registrationDate = $customer != null ? $customer->getCreatedAt() : null;

        foreach($orders as $order) {
            /* @var $order \Magento\Sales\Model\Order\Interceptor */
            $totalOrderAmount+= $order->getBaseGrandTotal();

            if($index == 0) {
                $firstOrderDate = $order->getCreatedAt();
            }

            if($index + 1 == $totalOrderCount) {
                $lastOrderDate = $order->getCreatedAt();
            }

            $index++;
        }

        return new CustomerHistoricInfo($email, $totalOrderCount, $totalOrderAmount, $firstOrderDate, $lastOrderDate, $registrationDate);
    }

    /**
     * @param string $email
     */
    public function getCustomerOrdersByCustomerEmail($email) {
        /* @var $orderFactory OrderFactory */ $orderFactory = ObjectManager::getInstance()->create(OrderFactory::class);
        $orders = $orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_email', $email)
            ->addOrder('created_at')
            ->getItems();

        return $orders;
    }
}