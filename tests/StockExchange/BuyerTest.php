<?php

namespace StockExchange;

use StockExchange\StockExchange\Buyer;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Symbol;

class BuyerTest extends TestCase
{
    public function testItAddsAShare()
    {
        $buyer = Buyer::create();
        $share = Share::fromSymbol(Symbol::fromValue('FOO'));

        $buyer->addShare($share);

        $this->assertCount(1, $buyer->shares());
        /** @var Share $buyersShare */
        $buyersShare = current($buyer->shares()->toArray());
        $this->assertEquals($share->id()->toString(), $buyersShare->id()->toString());
    }
}
