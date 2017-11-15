<?php

namespace Gracious\Interconnect\Http\Request\Data\Subscriber;

use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Magento\Newsletter\Model\Subscriber;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Subscriber
 */
class Factory extends FactoryAbstract
{

    /**
     * @param Subscriber $subscriber
     * @return string[]
     */
    public function setupData(Subscriber $subscriber)
    {
        $subscriberId = $subscriber->getId();
        $prefixedSubscriberId = $this->generateEntityId($subscriberId, EntityType::NEWSLETTER_SUBSCRIPTION);

        return [
            'subscriptionId' => $prefixedSubscriberId,
            'emailAddress' => $subscriber->getEmail(),
            'optIn' => $subscriber->isSubscribed(),
            'createdAt' => Formatter::formatDateStringToIso8601($subscriber->getCreatedAt()),
            'updatedAt' => Formatter::formatDateStringToIso8601($subscriber->getUpdatedAt())
        ];
    }
}