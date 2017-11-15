<?php

namespace Gracious\Interconnect\Http\Request\Data\Customer;

use Exception;
use Gracious\Interconnect\Http\Request\Data\Address\Factory as AddressFactory;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;
use Gracious\Interconnect\Model\Customer as InterconnectCustomer;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\PriceCents;
use Gracious\Interconnect\System\InvalidArgumentException;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface as CustomerContract;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Customer
 */
class Factory extends FactoryAbstract
{

    /**
     * @param CustomerContract|Customer $customer
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
            'customerId' => $this->generateEntityId($customerId, EntityType::CUSTOMER),
            'firstName' => $customer->getFirstname(),
            'lastName' => Formatter::prefixLastName($customer->getLastname(), $prefix),
            'emailAddress' => $customerEmail,
            'gender' => $customer->getGender(),
            'birthDate' => $customer->getDob(),
            'optIn' => $this->isCustomerSubscribedToNewsletter($customerId),
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

        // Nasty: Magento throws an exception if the address doesn't exist instead of just returning null
        try {
            /* @var $address Address */
            $address = $addressRepository->getById($addressId);
        } catch (Exception $e) {
            return null;
        }

        return $this->setupAddressData($address);
    }

    /**
     * @param Address|\Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Customer\Api\Data\AddressInterface $address
     * @return array|null
     */
    protected function setupAddressData($address)
    {
        if ($address === null) {
            return null;
        }

        $addressFactory = new AddressFactory();

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