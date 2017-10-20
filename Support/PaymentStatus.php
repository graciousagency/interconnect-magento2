<?php
namespace Gracious\Interconnect\Support;


abstract class PaymentStatus
{
    const PAID              = 'paid';
    const PARTIALLY_PAID    = 'partially_paid';
    const NOT_PAID          = 'not_paid';
}