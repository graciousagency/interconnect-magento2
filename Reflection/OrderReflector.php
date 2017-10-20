<?php
namespace Gracious\Interconnect\Reflection;

use Magento\Sales\Model\Order;
use Gracious\Interconnect\Support\PaymentStatus;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class OrderInspector
 * @package Gracious\Interconnect\Helper
 */
class OrderReflector extends AbstractHelper
{

    /**
     * @param Order $order
     * @return string
     */
    public function getOrderPaymentStatus(Order $order) {
        $total = $order->getBaseGrandTotal();
        $totalPaid = $order->getTotalPaid();
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