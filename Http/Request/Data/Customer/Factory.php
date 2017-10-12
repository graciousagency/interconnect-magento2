<?php
namespace Gracious\Interconnect\Http\Request\Data\Customer;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Model\Data\Address;
use Magento\Framework\App\ObjectManager;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\EntityType;
use Magento\Customer\Api\AddressRepositoryInterface;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Customer
 */
class Factory extends FactoryAbstract
{

    /**
     * @param Customer $customer
     * @return array
     */
    public function setupData(Customer $customer) {
        $prefix = $customer->getPrefix();
        $customerId = $customer->getId();

        return [
            'customerId'                => $this->generateEntityId($customerId, EntityType::CUSTOMER),
            'firstName'                 => $customer->getFirstname(),
            'lastName'                  => Formatter::prefixLastName($customer->getLastname(), $prefix),
            'emailAddress'              => $customer->getEmail(),
            'gender'                    => $customer->getGender(),
            'birthDate'                 => $customer->getDob(),
            'subscribe'                 => $this->isCustomerSubscribedToNewsletter($customerId),
            'billingAddress'            => $this->getAddress($customer->getDefaultBilling()),
            'shippingAddress'           => $this->getAddress($customer->getDefaultShipping()),
            'createdAt'                 => Formatter::formatDateStringToIso8601($customer->getCreatedAt()),
            'updatedAt'                 => Formatter::formatDateStringToIso8601($customer->getUpdatedAt())
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

        if(!($address instanceof Address)) {
            return null;
        }

        return [
            'addressId'     => $this->generateEntityId($addressId, EntityType::ADDRESS),
            'street'        => $address->getStreet(),
            'zipcode'       => $address->getPostcode(),
            'city'          => $address->getCity(),
            'country'       => $address->getCountryId(),
            'company'       => $address->getCompany()
        ];
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