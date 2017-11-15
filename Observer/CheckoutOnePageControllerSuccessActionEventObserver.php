<?php

namespace Gracious\Interconnect\Observer;

use Gracious\Interconnect\Generic\Behaviours\SendsOrder;
use Gracious\Interconnect\Support\Validation\RegEx;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

/**
 * Class OrderObserver
 * @package Gracious\Interconnect\Observer
 */
class CheckoutOnePageControllerSuccessActionEventObserver extends ObserverAbstract
{
    use SendsOrder;

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isComplete()) {
            $this->logger->alert(__METHOD__ . '=> Unable to send data; the module\'s config values are not configured in the backend. Aborting....');

            return;
        }

        $orderIds = $observer->getDataByKey('order_ids');

        if (!is_array($orderIds) || empty($orderIds)) {
            $this->logger->alert(__METHOD__ . '=> Expected to get an order id but none was provided! Aborting....');

            return;
        }

        $orderId = $orderIds[0];

        if (!RegEx::test(RegEx::INT, (string)$orderId)) {
            $this->logger->alert(__METHOD__ . '=> Invalid order id (' . json_encode($orderId) . ') Aborting....');

            return;
        }

        $orderRepository = ObjectManager::getInstance()->create(OrderRepository::class);
        /* @var $order Order */
        $order = $orderRepository->get($orderId);

        if ($order === null || $order->getId() != $orderId) {
            $this->logger->alert(__METHOD__ . '=> No order found for id(' . $orderId . ') Aborting....');

            return;
        }

        $this->sendOrder($order, $this->logger, $this->client);
    }
}