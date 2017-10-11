<?php
namespace Gracious\Interconnect\Http\Request\Data\Subscriber;

use Magento\Newsletter\Model\Subscriber;
use Gracious\Interconnect\Support\Formatter;
use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Http\Request\Data\FactoryAbstract;

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
    public function setupData(Subscriber $subscriber) {
        $subscriberId = $subscriber->getId();
        $prefixedSubscriberId = $this->generateEntityId($subscriberId, EntityType::NEWSLETTER_SUBSCRIPTION);

        $data = [
            'subscriptId'           => $prefixedSubscriberId,
            'emailAddress'          => $subscriber->getEmail(),
            'subscribe'             => $subscriber->isSubscribed(),
            'createdAt'             => Formatter::formatDateStringToIso8601($subscriber->getCreatedAt()),
            'updatedAt'             => Formatter::formatDateStringToIso8601($subscriber->getUpdatedAt())
        ];

        $this->logger->notice('Subscriber data: ' . json_encode($data));

        return $data;
    }
}