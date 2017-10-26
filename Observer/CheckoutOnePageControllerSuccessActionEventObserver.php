<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderRepository;
use Gracious\Interconnect\Support\Validation\RegEx;
use Gracious\Interconnect\Observer\ObserverAbstract;
use Gracious\Interconnect\Generic\Behaviours\SendsOrder;
use Gracious\Interconnect\Http\Request\Client as InterconnectClient;
use Gracious\Interconnect\Http\Request\Data\Order\Factory as OrderDataFactory;

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
        if(!$this->config->isComplete()) {
            $this->logger->alert(__METHOD__ . '=> Unable to send data; the module\'s config values are not configured in the backend. Aborting....');

            return;
        }

        $orderIds = $observer->getDataByKey('order_ids');

        if(!is_array($orderIds) || empty($orderIds)) {
            $this->logger->alert(__METHOD__ . '=> Expected to get an order id but none was provided! Aborting....');

            return;
        }

        $orderId = $orderIds[0];

        if(!RegEx::test(RegEx::INT, (string)$orderId)) { // don't trust Magento entirely here... There's something in the array but is it an integer?
            $this->logger->alert(__METHOD__ . '=> Invalid order id (' . json_encode($orderId) . ') Aborting....');

            return;
        }

        $orderRepository = ObjectManager::getInstance()->create(OrderRepository::class);
        /* @var $order Order */ $order = $orderRepository->get($orderId);

        if ($order === null || $order->getId() != $orderId) {
            $this->logger->alert(__METHOD__ . '=> No order found for id(' . $orderId . ') Aborting....');

            return;
        }

        $this->sendOrder($order, $this->logger, $this->client);
    }
}