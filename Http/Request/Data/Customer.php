<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Exception;
use Gracious\Interconnect\Model\Customer as InterconnectCustomer;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\System\InvalidArgumentException;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerContract;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Data\Address as AddressModel;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Customer
 */
class Customer extends Data
{

    /**
     * @param CustomerContract|CustomerModel $customer
     * @return array
     * @throws InvalidArgumentException
     */
    public function setupData($customer)
    {
        if (!($customer instanceof Customer) && !($customer instanceof CustomerContract)) {
            throw new InvalidArgumentException('Invalid argument supplied; expected instance of ' . Customer::class . ' or ' . CustomerContract::class);
        }

        $prefix = $customer->getPrefix();
        $customerId = $customer->getId();
        $customerEmail = $customer->getEmail();
        $interconnectCustomer = new InterconnectCustomer($customerEmail, $customer);
        $historicInfo = $interconnectCustomer->getCustomerHistoricInfo();

        return [
            'storeId' => $customer->getStoreId(),
            'customerId' => $this->generateEntityId($customerId, EntityType::CUSTOMER),
            'firstName' => $customer->getFirstname(),
            'lastName' => Formatter::prefixLastName($customer->getLastname(), $prefix),
            'emailAddress' => $customerEmail,
            'gender' => $customer->getGender(),
            'birthDate' => $customer->getDob(),
            'billingAddress' => $this->getAddress($customer->getDefaultBilling()),
            'shippingAddress' => $this->getAddress($customer->getDefaultShipping()),
            'isAnonymous' => false,
            'totalOrderCount' => (int)$historicInfo->getTotalOrderCount(),
            'totalOrderAmount' => PriceCents::create($historicInfo->getTotalOrderAmount())->toInt(),
            'firstOrderDate' => Formatter::formatDateStringToIso8601($historicInfo->getFirstOrderDate()),
            'lastOrderDate' => Formatter::formatDateStringToIso8601($historicInfo->getLastOrderDate()),
            'registrationDate' => Formatter::formatDateStringToIso8601($historicInfo->getRegistrationDate()),
            'createdAt' => Formatter::formatDateStringToIso8601($customer->getCreatedAt()),
            'updatedAt' => Formatter::formatDateStringToIso8601($customer->getUpdatedAt())
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function setUpAnonymousCustomerDataFromOrder(Order $order)
    {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $customerEmail = $billingAddress->getEmail();
        $interconnectCustomer = new InterconnectCustomer($customerEmail);
        $historicInfo = $interconnectCustomer->getCustomerHistoricInfo();

        return [
            'storeId' => $order->getStoreId(),
            'customerId' => null,
            'firstName' => $billingAddress->getFirstname(),
            'lastName' => Formatter::prefixLastName($billingAddress->getLastname(), $billingAddress->getPrefix()),
            'emailAddress' => $customerEmail,
            'gender' => null,
            'birthDate' => null,
            'optIn' => null,
            'billingAddress' => $this->setupAddressData($billingAddress),
            'shippingAddress' => $this->setupAddressData($shippingAddress),
            'isAnonymous' => true,
            'totalOrderCount' => (int)$historicInfo->getTotalOrderCount(),
            'totalOrderAmountInCents' => PriceCents::create($historicInfo->getTotalOrderAmount())->toInt(),
            'firstOrderDate' => Formatter::formatDateStringToIso8601($historicInfo->getFirstOrderDate()),
            'lastOrderDate' => Formatter::formatDateStringToIso8601($historicInfo->getLastOrderDate()),
            'registrationDate' => null,
            'createdAt' => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'updatedAt' => Formatter::formatDateStringToIso8601($order->getUpdatedAt())
        ];
    }

    /**
     * @param int $addressId
     * @return string[]|null
     */
    protected function getAddress($addressId)
    {
        if ($addressId === null) {
            return null;
        }

        /* @var $address Address */
        $address = null;

        /* @var AddressRepositoryInterface $addressRepository */
        $addressRepository = ObjectManager::getInstance()->create(AddressRepositoryInterface::class);

        try {
            /* @var $address AddressModel */
            $address = $addressRepository->getById($addressId);
        } catch (Exception $e) {
            return null;
        }

        return $this->setupAddressData($address);
    }

    /**
     * @param AddressModel|\Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Customer\Api\Data\AddressInterface $address
     * @return array|null
     */
    protected function setupAddressData($address)
    {
        if ($address === null) {
            return null;
        }

        $addressFactory = new Address();

        return $addressFactory->setupData($address);
    }

    /**
     * @param int $customerID
     * @return bool
     */
    protected function isCustomerSubscribedToNewsletter($customerID)
    {
        /* @var $utilitySubscriber Subscriber */
        $utilitySubscriber = ObjectManager::getInstance()->create(Subscriber::class);
        $checkSubscriber = $utilitySubscriber->loadByCustomerId($customerID);

        return $checkSubscriber->isSubscribed();
    }
}