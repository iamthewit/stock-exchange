<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Share;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;

class ShareTest extends TestCase
{

    public function testItTransfersOwnershipToBuyer()
    {
        $buyer = Trader::create(Uuid::uuid4());
        $share = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $share->transferOwnershipToTrader($buyer);

        $this->assertEquals($buyer->id()->toString(), $share->ownerId()->toString());
    }
}
