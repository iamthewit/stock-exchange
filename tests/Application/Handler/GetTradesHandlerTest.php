<?php

namespace StockExchange\Tests\Application\Handler;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Handler\GetTradesHandler;
use PHPUnit\Framework\TestCase;
use StockExchange\Application\Query\GetTradesQuery;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\Trade;
use StockExchange\StockExchange\TradeCollection;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\TraderCollection;

class GetTradesHandlerTest extends TestCase
{
    public function testItReturnsATradeCollection()
    {
        $exchange = Exchange::create(Uuid::uuid4());
        $traderJohnId = Uuid::uuid4();
        $exchange->createTrader($traderJohnId);
        $traderJohn = $exchange->traders()->findById($traderJohnId);

        $traderDaveId = Uuid::uuid4();
        $exchange->createTrader($traderDaveId);
        $traderDave = $exchange->traders()->findById($traderDaveId);

        $shareFoo1Id = Uuid::uuid4();
        $exchange->createShare($shareFoo1Id, Symbol::fromValue('FOO'));
        $exchange->allocateShareToTrader(
            $exchange->shares()->findById($shareFoo1Id),
            $traderJohn
        );

        $exchange->ask(
            Uuid::uuid4(),
            $traderJohn,
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $exchange->bid(
            Uuid::uuid4(),
            $traderDave,
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $exchangeReadRepository = $this->createMock(ExchangeReadRepositoryInterface::class);
        $exchangeReadRepository
            ->method('findById')
            ->willReturn($exchange);

        $handler = new GetTradesHandler($exchangeReadRepository);
        $trades = $handler(new GetTradesQuery($exchange->id()));

        $this->assertInstanceOf(TradeCollection::class, $trades);
    }
}
