<?php
namespace Gracious\Interconnect\Support;

/**
 * Class ProductType
 * @package Gracious\Interconnect\Support
 * 'Enum' for product type handles
 */
abstract class ProductType
{
    const SIMPLE        = 'simple';
    const BUNDLE        = 'bundle';
    const VIRTUAL       = 'virtual';
    const GROUPED       = 'grouped';
    const CONFIGURABLE  = 'configurable';
    const DOWNLOADABLE  = 'downloadable';
}