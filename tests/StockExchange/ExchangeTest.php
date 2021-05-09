<?php

namespace StockExchange;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\Trade;
use StockExchange\StockExchange\TradeCollection;

class ExchangeTest extends TestCase
{
    public function testABidCanBeMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::fromSymbol($symbol));

        $exchange->bid(
            Bid::create(
                Uuid::uuid4(),
                $seller,
                $symbol,
                Price::fromValue(100)
            )
        );

        $this->assertCount(1, $exchange->bids());
    }

    public function testAnAskCanBeMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $exchange->ask(
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                $symbol,
                Price::fromValue(100)
            )
        );

        $this->assertCount(1, $exchange->asks());
    }

    public function testSharesCanBeTradedWhenABidIsMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $buyer = Trader::create(Uuid::uuid4());
        $price = Price::fromValue(100);

        $exchange->ask(
            Ask::create(
                Uuid::uuid4(),
                $buyer,
                $symbol,
                $price
            )
        );

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::fromSymbol($symbol));

        $exchange->bid(
            Bid::create(
                Uuid::uuid4(),
                $seller,
                $symbol,
                $price
            )
        );
        
        // 1 trade occurred
        $this->assertCount(1, $exchange->trades());

        // bid and ask price match
        /** @var Trade $trade */
        $trade = current($exchange->trades()->toArray());
        $this->assertEquals($price->value(), $trade->bid()->price()->value());
        $this->assertEquals($price->value(), $trade->ask()->price()->value());

        // seller has 0 shares
        $this->assertCount(0, $seller->shares());

        // buyer has 1 share
        $this->assertCount(1, $buyer->shares());

        // buyer has 1 share of FOO
        /** @var Share $buyerShare */
        $buyerShare = current($buyer->shares()->toArray());
        $this->assertEquals($symbol->value(), $buyerShare->symbol()->value());
    }

    public function testSharesCanBeTradedWhenAnAskIsMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $buyer = Trader::create(Uuid::uuid4());
        $price = Price::fromValue(100);

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::fromSymbol($symbol));

        $exchange->bid(
            Bid::create(
                Uuid::uuid4(),
                $seller,
                $symbol,
                $price
            )
        );

        $exchange->ask(
            Ask::create(
                Uuid::uuid4(),
                $buyer,
                $symbol,
                $price
            )
        );

        // 1 trade occurred
        $this->assertCount(1, $exchange->trades());

        // bid and ask price match
        /** @var Trade $trade */
        $trade = current($exchange->trades()->toArray());
        $this->assertEquals($price->value(), $trade->bid()->price()->value());
        $this->assertEquals($price->value(), $trade->ask()->price()->value());

        // seller has 0 shares
        $this->assertCount(0, $seller->shares());

        // buyer has 1 share
        $this->assertCount(1, $buyer->shares());

        // buyer has 1 share of FOO
        /** @var Share $buyerShare */
        $buyerShare = current($buyer->shares()->toArray());
        $this->assertEquals($symbol->value(), $buyerShare->symbol()->value());
    }
}
