<?php

namespace StockExchange\Tests\StockExchange;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Event\Exchange\AskAddedToExchange;
use StockExchange\StockExchange\Event\Ask\AskCreated;
use StockExchange\StockExchange\Event\Exchange\AskRemovedFromExchange;
use StockExchange\StockExchange\Event\Exchange\BidAddedToExchange;
use StockExchange\StockExchange\Event\Bid\BidCreated;
use StockExchange\StockExchange\Event\Exchange\ExchangeCreated;
use StockExchange\StockExchange\Event\Exchange\BidRemovedFromExchange;
use StockExchange\StockExchange\Event\Exchange\ShareAddedToExchange;
use StockExchange\StockExchange\Event\Exchange\ShareAllocatedToTrader;
use StockExchange\StockExchange\Event\Exchange\TradeExecuted;
use StockExchange\StockExchange\Event\Exchange\TraderAddedToExchange;
use StockExchange\StockExchange\Event\Trader\TraderAddedShare;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\Trade;
use StockExchange\StockExchange\TradeCollection;
use StockExchange\StockExchange\TraderCollection;

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
            new TradeCollection([]),
            new TraderCollection([]),
            new ShareCollection([])
        );

        $buyerId = Uuid::uuid4();
        $exchange->createTrader($buyerId);
        $buyer = $exchange->traders()->findById($buyerId);

        $exchange->bid(
            Uuid::uuid4(),
            $buyer,
            $symbol,
            Price::fromValue(100)
        );

        $this->assertCount(1, $exchange->bids());

        // assert that the domain will dispatch the bid events in the correct order
        // TODO: do we really need to test this sort of thing?
//        $this->assertCount(3, $exchange->dispatchableEvents());
//        $this->assertInstanceOf(BidCreated::class, $exchange->dispatchableEvents()[1]);
//        $this->assertInstanceOf(BidAddedToExchange::class, $exchange->dispatchableEvents()[2]);
    }

    public function testAnAskCanBeMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([]),
            new TraderCollection([]),
            new ShareCollection([])
        );

        $price = Price::fromValue(100);

        $sellerId = Uuid::uuid4();
        $exchange->createTrader($sellerId);
        $seller = $exchange->traders()->findById($sellerId);

        $shareId = Uuid::uuid4();
        $exchange->createShare($shareId, $symbol);
        $share = $exchange->shares()->findById($shareId);
        $exchange->allocateShareToTrader($share, $seller);

        $exchange->ask(
            Uuid::uuid4(),
            $seller,
            $symbol,
            $price
        );

        $this->assertCount(1, $exchange->asks());

        // assert that the domain will dispatch the ask events in the correct order
        // TODO: do we really need to test this sort of thing?
//        $this->assertCount(3, $exchange->dispatchableEvents());
//        $this->assertInstanceOf(AskCreated::class, $exchange->dispatchableEvents()[1]);
//        $this->assertInstanceOf(AskAddedToExchange::class, $exchange->dispatchableEvents()[2]);
    }

    public function testSharesCanBeTradedWhenABidIsMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([]),
            new TraderCollection([]),
            new ShareCollection([])
        );

        $price = Price::fromValue(100);

        $sellerId = Uuid::uuid4();
        $exchange->createTrader($sellerId);
        $seller = $exchange->traders()->findById($sellerId);

        $shareId = Uuid::uuid4();
        $exchange->createShare($shareId, $symbol);
        $share = $exchange->shares()->findById($shareId);
        $exchange->allocateShareToTrader($share, $seller);

        $buyerId = Uuid::uuid4();
        $exchange->createTrader($buyerId);
        $buyer = $exchange->traders()->findById($buyerId);

        $exchange->ask(
            Uuid::uuid4(),
            $seller,
            $symbol,
            $price
        );

        $exchange->bid(
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

    public function testSharesCanBeTradedWhenAnAskIsMade()
    {
        $symbol = Symbol::fromValue('FOO');
        $exchange = Exchange::create(
            Uuid::uuid4(),
            new SymbolCollection([$symbol]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([]),
            new TraderCollection([]),
            new ShareCollection([])
        );

        $price = Price::fromValue(100);

        $sellerId = Uuid::uuid4();
        $exchange->createTrader($sellerId);
        $seller = $exchange->traders()->findById($sellerId);

        $shareId = Uuid::uuid4();
        $exchange->createShare($shareId, $symbol);
        $share = $exchange->shares()->findById($shareId);
        $exchange->allocateShareToTrader($share, $seller);

        $buyerId = Uuid::uuid4();
        $exchange->createTrader($buyerId);
        $buyer = $exchange->traders()->findById($buyerId);

        $exchange->bid(
            Uuid::uuid4(),
            $buyer,
            $symbol,
            $price
        );

        $exchange->ask(
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

    public function testStateCanBeRestoredFromEvents()
    {
        $traderOne = Trader::create(Uuid::uuid4());
        $traderTwo = Trader::create(Uuid::uuid4());

        $shareFoo = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $shareBar = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $bid = Bid::create(
            Uuid::uuid4(),
            $traderOne,
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask = Ask::create(
            Uuid::uuid4(),
            $traderTwo,
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
                    new TradeCollection([]),
                    new TraderCollection([]),
                    new ShareCollection([])
                )
            ),

            // traders
            new TraderAddedToExchange($traderOne),
            new TraderAddedToExchange($traderTwo),

            // shares
            new ShareAddedToExchange($shareFoo),
            new ShareAddedToExchange($shareBar),

            // allocate shares to traders
            new ShareAllocatedToTrader($shareFoo, $traderOne),
            new ShareAllocatedToTrader($shareBar, $traderTwo),

            new BidAddedToExchange($bid),
            new AskAddedToExchange($ask),
            new BidRemovedFromExchange($bid),
            new AskRemovedFromExchange($ask),
            new TradeExecuted(Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask))
        ];

        $exchange = Exchange::restoreStateFromEvents($events);

//        d($exchange);

        $this->assertInstanceOf(Exchange::class, $exchange);
        $this->assertCount(12, $exchange->appliedEvents());
        $this->assertInstanceOf(ExchangeCreated::class, $exchange->appliedEvents()[0]);
        $this->assertInstanceOf(TraderAddedToExchange::class, $exchange->appliedEvents()[1]);
        $this->assertInstanceOf(TraderAddedToExchange::class, $exchange->appliedEvents()[2]);
        $this->assertInstanceOf(ShareAddedToExchange::class, $exchange->appliedEvents()[3]);
        $this->assertInstanceOf(ShareAddedToExchange::class, $exchange->appliedEvents()[4]);
        $this->assertInstanceOf(ShareAllocatedToTrader::class, $exchange->appliedEvents()[5]);
        $this->assertInstanceOf(ShareAllocatedToTrader::class, $exchange->appliedEvents()[5]);
        $this->assertInstanceOf(BidAddedToExchange::class, $exchange->appliedEvents()[7]);
        $this->assertInstanceOf(AskAddedToExchange::class, $exchange->appliedEvents()[8]);
        $this->assertInstanceOf(BidRemovedFromExchange::class, $exchange->appliedEvents()[9]);
        $this->assertInstanceOf(AskRemovedFromExchange::class, $exchange->appliedEvents()[10]);
        $this->assertInstanceOf(TradeExecuted::class, $exchange->appliedEvents()[11]);

        $this->assertCount(0, $exchange->bids());
        $this->assertCount(0, $exchange->asks());
        $this->assertCount(1, $exchange->trades());
    }

    public function testExchangeStateAndTraderStateAndShareStateAreRestored()
    {
        $this->markTestIncomplete();
        $events = [];

        // Exchange was created
        $events[] = new ExchangeCreated(
            Exchange::create(
                Uuid::uuid4(),
                new SymbolCollection([]),
                new BidCollection([]),
                new AskCollection([]),
                new TradeCollection([]),
                new TraderCollection([]),
                new ShareCollection([])
            )
        );

        $shareFoo = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $traderBill = Trader::create(Uuid::uuid4());

        // share was created
        $events[] = new ShareAddedToExchange($shareFoo);
        // trader was created
        $events[] = new TraderAddedToExchange($traderBill);
        // share was allocated to trader
        $events[] = new ShareAllocatedToTrader($shareFoo, $traderBill);


        $shareBar = Share::create(Uuid::uuid4(), Symbol::fromValue('BAR'));
        $traderBen = Trader::create(Uuid::uuid4());

        // share was created
        $events[] = new ShareAddedToExchange($shareBar);
        // trader was created
        $events[] = new TraderAddedToExchange($traderBen);
        // share was allocated to trader
        $events[] = new ShareAllocatedToTrader($shareBar, $traderBen);

        $bidForFoo = Bid::create(
            Uuid::uuid4(),
            $traderBen,
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        // bid for share FOO was created
        $events[] = new BidAddedToExchange($bidForFoo);

        $askForFoo = Ask::create(
            Uuid::uuid4(),
            $traderBill,
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        // ask for share FOO was created
        $events[] = new AskAddedToExchange($askForFoo);

        $exchange = Exchange::restoreStateFromEvents($events);
    }
}
