<?php
namespace Gracious\Interconnect\Support;

/**
 * Class EntityType
 * @package Gracious\Interconnect\Support
 * 'Enum' for all the types of entities we send
 */
abstract class EntityType
{
    const NEWSLETTER_SUBSCRIPTION           = 'NewsletterSubscription';
    const CUSTOMER                          = 'Customer';
    const ORDER                             = 'Order';
    const ORDER_ITEM                        = 'OrderItem';
    const QUOTE                             = 'Quote';
    const QUOTE_ITEM                        = 'QuoteItem';
    const PRODUCT                           = 'Product';
    const ADDRESS                           = 'Address';
}