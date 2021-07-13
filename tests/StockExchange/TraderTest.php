<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Trader;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Symbol;

class TraderTest extends TestCase
{
    public function testItAddsAShare()
    {
        $share = Share::fromSymbol(Symbol::fromValue('FOO'));
        $trader = Trader::create(Uuid::uuid4());

        $trader->addShare($share);

        $this->assertCount(1, $trader->shares());
        /** @var Share $tradersShare */
        $tradersShare = current($trader->shares()->toArray());
        $this->assertEquals($share->id()->toString(), $tradersShare->id()->toString());
    }

    public function testItRemovesAShare()
    {
        $share = Share::fromSymbol(Symbol::fromValue('FOO'));
        $trader = Trader::create(Uuid::uuid4());
        $trader->addShare($share);

        $trader->removeShare($share);

        $this->assertCount(0, $trader->shares());
    }

    public function testItTransformsToJSON()
    {
        $this->markTestIncomplete();
        $trader = Trader::create(Uuid::uuid4());

        $trader->addShare(Share::fromSymbol(Symbol::fromValue('FOO')));
        $trader->addShare(Share::fromSymbol(Symbol::fromValue('BAR')));

//        \Kint::dump(json_encode($trader));die;
    }
}
