<?php

namespace StockExchange;

use StockExchange\StockExchange\Buyer;
use StockExchange\StockExchange\Share;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;

class ShareTest extends TestCase
{

    public function testItTransfersOwnershipToBuyer()
    {
        $buyer = Buyer::create();
        $share = Share::fromSymbol(Symbol::fromValue('FOO'));

        $share->transferOwnershipToBuyer($buyer);

        $this->assertEquals($buyer->id()->toString(), $share->ownerId()->toString());
    }
}
