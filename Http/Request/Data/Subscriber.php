<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\Formatter;
use Magento\Newsletter\Model\Subscriber as SubscriberModel;

/**
 * Class Factory
 * @package Gracious\Interconnect\Http\Request\Data\Subscriber
 */
class Subscriber extends Data
{

    /**
     * @param SubscriberModel $subscriber
     * @return string[]
     */
    public function setupData(SubscriberModel $subscriber)
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