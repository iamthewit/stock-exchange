<?php

namespace StockExchange\Tests\Application\Handler;

use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\Query;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Handler\GetExchangeByIdHandler;
use PHPUnit\Framework\TestCase;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Event\Exchange\ExchangeCreated;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\TradeCollection;
use StockExchange\StockExchange\TraderCollection;

class GetExchangeByIdHandlerTest extends TestCase
{
    public function testItGetsExchangeById()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create($exchangeId);

        $exchangeReadRepository = $this->createMock(ExchangeReadRepositoryInterface::class);
        $exchangeReadRepository
            ->method('findById')
            ->willReturn($exchange);

        $getExchangeByIdQuery = new GetExchangeByIdQuery($exchangeId);
        $getExchangeByIdHandler = new GetExchangeByIdHandler($exchangeReadRepository);
        $retrievedExchange = $getExchangeByIdHandler($getExchangeByIdQuery);

        $this->assertInstanceOf(Exchange::class, $retrievedExchange);
        $this->assertEquals($exchangeId->toString(), $retrievedExchange->id()->toString());
    }
}
