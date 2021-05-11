<?php

namespace StockExchange\Tests\StockExchange;

use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\ShareCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;

class ShareCollectionTest extends TestCase
{

    public function testItFiltersBySymbol()
    {
        $collection = new ShareCollection([
            Share::fromSymbol(
                Symbol::fromValue('FOO'),
            ),
            Share::fromSymbol(
                Symbol::fromValue('FOO'),
            ),
            Share::fromSymbol(
                Symbol::fromValue('BAR'),
            ),
            Share::fromSymbol(
                Symbol::fromValue('BAR'),
            ),
        ]);

        $filteredCollection = $collection->filterBySymbol(
            Symbol::fromValue('FOO'),
        );

        $this->assertCount(2, $filteredCollection);
    }
}
