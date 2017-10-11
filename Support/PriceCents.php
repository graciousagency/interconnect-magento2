<?php
namespace Gracious\Interconnect\Support;

/**
 * Class PriceCents
 * @package Gracious\Interconnect\Support
 */
class PriceCents
{

    /**
     * @var float|int
     */
    protected $price = 0;

    /**
     * PriceCents constructor.
     * @param float $price
     */
    function __construct($price)
    {
        $this->price = (float)$price;
    }

    /**
     * @return float|int
     */
    public function toInt()
    {
        return (int)100 * $this->price;
    }

    /**
     * @param float|int $price
     * @return static
     */
    public static function create($price)
    {
        return new static($price);
    }
}