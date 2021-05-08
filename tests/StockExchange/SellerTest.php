<?php

namespace StockExchange;

use StockExchange\StockExchange\Seller;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\Symbol;

class SellerTest extends TestCase
{

    public function testItRemovesAShare()
    {
        $share = Share::fromSymbol(Symbol::fromValue('FOO'));
        $seller = Seller::create(
            new ShareCollection([$share])
        );

        $seller->removeShare($share);

        $this->assertCount(0, $seller->shares());
    }
}
