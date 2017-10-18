<?php
namespace Gracious\Interconnect\Observer;

use Throwable;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\OrderRepository;
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
            $this->logger->error(__METHOD__.' :: Unable to rock and roll: module config values not configured (completely) in the backend. Aborting....');

            return;
        }

        $orderIds = $observer->getDataByKey('order_ids');

        if(!is_array($orderIds) || empty($orderIds)) {
            $this->logger->alert(__METHOD__.' :: Expected to get an order id but none was provided! Aborting....');
            return;
        }

        $orderId = $orderIds[0];
        /* @var $order Order */ $order = $orderRepository = ObjectManager::getInstance()->create(OrderRepository::class)->get($orderId);

        $this->sendOrder($order, $this->logger, $this->client);
    }
}