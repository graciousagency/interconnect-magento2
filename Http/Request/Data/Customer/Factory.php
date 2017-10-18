<?php
namespace Gracious\Interconnect\Http\Request\Data\Customer;

use Exception;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\EntityType;
use Magento\Customer\Api\AddressRepositoryInterface;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;
use Magento\Customer\Api\Data\CustomerInterface as CustomerContract;
use Gracious\Interconnect\Http\Request\Data\Address\Factory as AddressFactory;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Customer
 */
class Factory extends FactoryAbstract
{

    /**
     * @param CustomerContract|Customer $customer
     * @return array
     */
    public function setupData($customer) {
        if(!($customer instanceof Customer) && !($customer instanceof CustomerContract)) {
            throw new Exception('Invalid argument supplied; expected instance of '.Customer::class.' or '.CustomerContract::class);
        }

        $prefix = $customer->getPrefix();
        $customerId = $customer->getId();

        return [
            'customerId'                => $this->generateEntityId($customerId, EntityType::CUSTOMER),
            'firstName'                 => $customer->getFirstname(),
            'lastName'                  => Formatter::prefixLastName($customer->getLastname(), $prefix),
            'emailAddress'              => $customer->getEmail(),
            'gender'                    => $customer->getGender(),
            'birthDate'                 => $customer->getDob(),
            'optIn'                     => $this->isCustomerSubscribedToNewsletter($customerId),
            'billingAddress'            => $this->getAddress($customer->getDefaultBilling()),
            'shippingAddress'           => $this->getAddress($customer->getDefaultShipping()),
            'isAnonymous'               => false,
            'createdAt'                 => Formatter::formatDateStringToIso8601($customer->getCreatedAt()),
            'updatedAt'                 => Formatter::formatDateStringToIso8601($customer->getUpdatedAt())
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    public function setUpAnonymousCustomerDataFromOrder(Order $order) {
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        return [
            'customerId'                => null,
            'firstName'                 => $billingAddress->getFirstname(),
            'lastName'                  => Formatter::prefixLastName($billingAddress->getLastname(), $billingAddress->getPrefix()),
            'emailAddress'              => $billingAddress->getEmail(),
            'gender'                    => null,
            'birthDate'                 => null,
            'optIn'                     => null,
            'billingAddress'            => $this->setupAddressData($billingAddress),
            'shippingAddress'           => $this->setupAddressData($shippingAddress),
            'isAnonymous'               => true,
            'createdAt'                 => Formatter::formatDateStringToIso8601($order->getCreatedAt()),
            'updatedAt'                 => Formatter::formatDateStringToIso8601($order->getUpdatedAt())
        ];
    }

    /**
     * @param int $addressId
     * @return string[]|null
     */
    protected function getAddress($addressId) {
        if($addressId === null) {
            return null;
        }

        /* @var $address Address */ $address = null;
        /* @var AddressRepositoryInterface $addressRepository */ $addressRepository = ObjectManager::getInstance()->create(AddressRepositoryInterface::class);

        // Nasty: Magento throws an exception if the address doesn't exist instead of just returning null
        try {
            /* @var $address Address */ $address = $addressRepository->getById($addressId);
        } catch (Exception $e) {
            return null;
        }

        return $this->setupAddressData($address);
    }

    /**
     * @param Address|\Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Customer\Api\Data\AddressInterface $address
     * @return array|null
     */
    protected function setupAddressData($address) {
        if($address === null) {
            return null;
        }

        $addressFactory = new AddressFactory();

        return $addressFactory->setupData($address);
    }

    /**
     * @param int $customerID
     * @return bool
     */
    protected function isCustomerSubscribedToNewsletter($customerID) {
        /* @var $utilitySubscriber Subscriber */ $utilitySubscriber = ObjectManager::getInstance()->create(Subscriber::class);
        $checkSubscriber = $utilitySubscriber->loadByCustomerId($customerID);

        return $checkSubscriber->isSubscribed();
    }
}