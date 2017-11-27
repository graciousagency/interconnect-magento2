<?php

namespace Gracious\Interconnect\Http\Request\Data;

use Gracious\Interconnect\Support\EntityType;
use Gracious\Interconnect\Support\PriceCents;
use Magento\Sales\Model\Order;

class Invoice extends Data
{
    /**
     * @param Order\Invoice $invoice
     * @return array
     */
    public function setupData(Order\Invoice $invoice)
    {
        $quoteId = $invoice->getOrder()->getQuoteId();
        $prefixedQuoteId = (null !== $quoteId) ? $this->generateEntityId($quoteId, EntityType::QUOTE) : null;

        return [
            'orderId' => $this->generateEntityId($invoice->getOrderId(), EntityType::ORDER),
            'quoteId' => $prefixedQuoteId,
            'incrementId' => $invoice->getOrder()->getIncrementId(),
            'paymentStatus' => (string) $invoice->getStateName($invoice->getState()),
            'totalAmountInCents' => PriceCents::create($invoice->getGrandTotal())->toInt(),
            'paymentMethod' => $invoice->getOrder()->getPayment()->getMethod()
        ];
    }
}
