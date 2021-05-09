<?php

namespace StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class AskCollectionTest extends TestCase
{
    public function testFilterBySymbolAndPrice()
    {
        $collection = new AskCollection([
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(200)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
        ]);

        $filteredCollection = $collection->filterBySymbolAndPrice(
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $this->assertCount(2, $filteredCollection);
    }
}
