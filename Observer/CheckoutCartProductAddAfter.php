<?php

namespace Gracious\Interconnect\Observer;

use Magento\Framework\Event\Observer as EventObserver;

class CheckoutCartProductAddAfter extends Observer
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        return;
    }
}
