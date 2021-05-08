<?php

namespace StockExchange;

use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Seller;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\Symbol;

class BidCollectionTest extends TestCase
{
    public function testFilterBySymbolAndPrice()
    {
        $collection = new BidCollection([
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
                Symbol::fromValue('BAR'),
                Price::fromValue(100)
            ),
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
                Symbol::fromValue('BAR'),
                Price::fromValue(200)
            ),
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Bid::create(
                Uuid::uuid4(),
                Seller::create(new ShareCollection([])),
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
