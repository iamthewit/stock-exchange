<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\ShareCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;

class ShareCollectionTest extends TestCase
{

    public function testItFiltersBySymbol()
    {
        $collection = new ShareCollection([
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('BAR')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('BAR')
            ),
        ]);

        $filteredCollection = $collection->filterBySymbol(
            Symbol::fromValue('FOO'),
        );

        $this->assertCount(2, $filteredCollection);
    }
}
