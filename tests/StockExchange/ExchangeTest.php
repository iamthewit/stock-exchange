<?php

namespace StockExchange\Tests\StockExchange;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Event\AskAddedToExchange;
use StockExchange\StockExchange\Event\AskCreated;
use StockExchange\StockExchange\Event\AskRemovedFromExchange;
use StockExchange\StockExchange\Event\BidAddedToExchange;
use StockExchange\StockExchange\Event\BidCreated;
use StockExchange\StockExchange\Event\ExchangeCreated;
use StockExchange\StockExchange\Event\BidRemovedFromExchange;
use StockExchange\StockExchange\Event\TradeExecuted;
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
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::create(Uuid::uuid4(), $symbol));

        $exchange->bid(
            Uuid::uuid4(),
            $seller,
            $symbol,
            Price::fromValue(100)
        );

        $this->assertCount(1, $exchange->bids());

        // assert that the domain will dispatch the bid events in the correct order
        $this->assertCount(2, $exchange->dispatchableEvents());

        // TODO: add this back in
//        $this->assertInstanceOf(BidCreated::class, $exchange->dispatchableEvents()[1]);
        $this->assertInstanceOf(BidAddedToExchange::class, $exchange->dispatchableEvents()[1]);
    }

    public function testAnAskCanBeMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $exchange->ask(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            $symbol,
            Price::fromValue(100)
        );

        $this->assertCount(1, $exchange->asks());

        // assert that the domain will dispatch the ask events in the correct order
        $this->assertCount(3, $exchange->dispatchableEvents());
        $this->assertInstanceOf(AskCreated::class, $exchange->dispatchableEvents()[1]);
        $this->assertInstanceOf(AskAddedToExchange::class, $exchange->dispatchableEvents()[2]);
    }

    public function testSharesCanBeTradedWhenABidIsMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $buyer = Trader::create(Uuid::uuid4());
        $price = Price::fromValue(100);

        $exchange->ask(
            Uuid::uuid4(),
            $buyer,
            $symbol,
            $price
        );

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::create(Uuid::uuid4(), $symbol));

        $exchange->bid(
            Uuid::uuid4(),
            $seller,
            $symbol,
            $price
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
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $buyer = Trader::create(Uuid::uuid4());
        $price = Price::fromValue(100);

        $seller = Trader::create(Uuid::uuid4());
        $seller->addShare(Share::create(Uuid::uuid4(), $symbol));

        $exchange->bid(
            Uuid::uuid4(),
            $seller,
            $symbol,
            $price
        );

        $exchange->ask(
            Uuid::uuid4(),
            $buyer,
            $symbol,
            $price
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

    public function testStateCanBeRestoredFromEvents()
    {
        $bid = Bid::create(
            Uuid::uuid4(),
            Trader::create(
                Uuid::uuid4()
            ),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask = Ask::create(
            Uuid::uuid4(),
            Trader::create(
                Uuid::uuid4()
            ),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $events = [
            new ExchangeCreated(
                Exchange::create(
                    Uuid::uuid4(),
                    new SymbolCollection([]),
                    new BidCollection([]),
                    new AskCollection([]),
                    new TradeCollection([])
                )
            ),
            new BidAddedToExchange($bid),
            new AskAddedToExchange($ask),
            new BidRemovedFromExchange($bid),
            new AskRemovedFromExchange($ask),
            new TradeExecuted(Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask))
        ];

        $exchange = Exchange::restoreStateFromEvents($events);

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertCount(6, $exchange->appliedEvents());
        $this->assertInstanceOf(ExchangeCreated::class, $exchange->appliedEvents()[0]);
        $this->assertInstanceOf(BidAddedToExchange::class, $exchange->appliedEvents()[1]);
        $this->assertInstanceOf(AskAddedToExchange::class, $exchange->appliedEvents()[2]);
        $this->assertInstanceOf(BidRemovedFromExchange::class, $exchange->appliedEvents()[3]);
        $this->assertInstanceOf(AskRemovedFromExchange::class, $exchange->appliedEvents()[4]);
        $this->assertInstanceOf(TradeExecuted::class, $exchange->appliedEvents()[5]);

        $this->assertCount(0, $exchange->bids());
        $this->assertCount(0, $exchange->asks());
        $this->assertCount(1, $exchange->trades());
    }
}
