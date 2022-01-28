<?php

namespace StockExchange\Tests\StockExchange;

use StockExchange\StockExchange\Symbol;
use PHPUnit\Framework\TestCase;

class SymbolTest extends TestCase
{
    public function testValue()
    {
        $symbol = Symbol::fromValue('FOO');

        $this->assertEquals('FOO', $symbol->value());
    }

    public function testToArray()
    {
        $symbol = Symbol::fromValue('FOO');

        $this->assertEquals(
            [
                'value' => 'FOO'
            ],
            $symbol->toArray()
        );
    }

    public function testJsonSerialize()
    {
        $symbol = Symbol::fromValue('FOO');

        $this->assertEquals(
            [
                'value' => 'FOO'
            ],
            $symbol->toArray()
        );
    }

    public function testJsonEncode()
    {
        $symbol = Symbol::fromValue('FOO');

        $this->assertEquals(
            json_encode([
                'value' => 'FOO'
            ]),
            json_encode($symbol)
        );
    }
}
