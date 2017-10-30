<?php
namespace Gracious\Interconnect\Model;

use Gracious\Interconnect\Support\PaymentStatus;
use Magento\Sales\Model\Order as SalesModelOrder;

class Order {

    /**
     * @var SalesModelOrder
     */
    protected $order;

    /**
     * Order constructor.
     * @param SalesModelOrder $order
     */
    public function __construct(SalesModelOrder $order) {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getOrderPaymentStatus() {
        $total = $this->order->getBaseGrandTotal();
        $totalPaid = $this->order->getTotalPaid();
        $amountRemaining = $total - $totalPaid;
        $paymentStatus = null;

        switch ($amountRemaining) {
            case $amountRemaining == 0:
                $paymentStatus = PaymentStatus::PAID;
                break;

            case $amountRemaining == $total:
                $paymentStatus = PaymentStatus::NOT_PAID;
                break;

            case $amountRemaining > 0 && $amountRemaining < $total;
            default:
                $paymentStatus = PaymentStatus::PARTIALLY_PAID;
                break;
        }

        return $paymentStatus;
    }
}