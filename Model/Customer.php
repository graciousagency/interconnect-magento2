<?php

namespace Gracious\Interconnect\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderFactory;

class Customer
{

    /**
     * @var string
     */
    protected $email;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer;

    /**
     * Customer constructor.
     * @param string $email
     * @param null $customer
     */
    public function __construct($email, $customer = null)
    {
        $this->email = $email;
        $this->customer = $customer;
        $this->loadCustomer();
    }

    protected function loadCustomer()
    {
        if ($this->customer === null) {
            /* @var $customerRepository CustomerRepositoryInterface */
            $customerRepository = ObjectManager::getInstance()->create(CustomerRepositoryInterface::class);

            try {
                /* @var $customer CustomerInterface */
                $customer = $customerRepository->get($this->email);
            } catch (Exception $exception) {
                $customer = null;
            }

            $this->customer = $customer;
        }
    }

    public function setupData()
    {

        $address = [
            'emailAddress' => $this->customer->getEmail(),
            'street' => '',
            'zipcode' => '',
            'city' => '',
            'country' => '',
            'company' => ''
        ];
        $data = [
            'customerId' => $this->customer->getId(),
            'firstName' => $this->customer->getFirstName(),
            'lastName' => $this->customer->getLastName(),
            'emailAddress' => $this->customer->getEmail(),
            'phoneNumber' => '',
            'gender' => '',
            'billingAddress' => $address,
            'shippingAddress' => $address,
            'isAnonymous' => true,
            'totalOrderCount' => 0,
            'totalOrderAmount' => 0,
        ];

        return $data;

    }

    /**
     * @return CustomerHistoricInfo
     */
    public function getCustomerHistoricInfo()
    {
        $orders = $this->getCustomerOrders();
        $totalOrderCount = count($orders);
        $totalOrderAmount = 0.00;
        $firstOrderDate = null;
        $lastOrderDate = null;
        $index = 0;
        $registrationDate = $this->customer != null ? $this->customer->getCreatedAt() : null;

        foreach ($orders as $order) {
            /* @var $order \Magento\Sales\Model\Order\Interceptor */
            $totalOrderAmount += $order->getBaseGrandTotal();

            if ($index == 0) {
                $firstOrderDate = $order->getCreatedAt();
            }

            if ($index + 1 == $totalOrderCount) {
                $lastOrderDate = $order->getCreatedAt();
            }

            $index++;
        }

        return new CustomerHistoricInfo($this->email, $totalOrderCount, $totalOrderAmount, $firstOrderDate, $lastOrderDate, $registrationDate);
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getCustomerOrders()
    {
        /* @var $orderFactory OrderFactory */
        $orderFactory = ObjectManager::getInstance()->create(OrderFactory::class);
        $orders = $orderFactory->create()
            ->getCollection()
            ->addFieldToFilter('customer_email', $this->email)
            ->addOrder('created_at')
            ->getItems();

        return $orders;
    }
}