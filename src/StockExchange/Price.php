<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

class Price
{
    private int $value;
    private string $currency; // TODO

    private function __construct()
    {
    }

    /**
     * @param int $value
     *
     * @return Price
     */
    public static function fromValue(int $value)
    {
        $price = new self();
        $price->value = $value;

        return $price;
    }

    /**
     * @return int
     */
    public function value(): int
    {
        return $this->value;
    }
}