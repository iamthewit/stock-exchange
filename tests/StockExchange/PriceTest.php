<?php

namespace StockExchange\Tests\StockExchange;

use StockExchange\StockExchange\Price;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{

    public function testValue()
    {
        $price = Price::fromValue(100);

        $this->assertEquals(100, $price->value());
    }

    public function testToArray()
    {
        $price = Price::fromValue(100);

        $this->assertEquals(
            [
                'value' => 100
            ],
            $price->toArray()
        );
    }

    public function testJsonSerialize()
    {
        $price = Price::fromValue(100);

        $this->assertEquals(
            [
                'value' => 100
            ],
            $price->toArray()
        );
    }

    public function testJsonEncodePrice()
    {
        $price = Price::fromValue(100);

        $this->assertEquals(
            json_encode([
                'value' => 100
            ]),
            json_encode($price)
        );
    }
}
