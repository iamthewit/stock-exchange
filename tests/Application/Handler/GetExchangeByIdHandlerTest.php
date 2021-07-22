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
use StockExchange\StockExchange\Event\ExchangeCreated;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\TradeCollection;

class GetExchangeByIdHandlerTest extends TestCase
{
    public function testItGetsExchangeById()
    {
        $exchangeId = Uuid::uuid4();
        $exchange = Exchange::create(
            $exchangeId,
            new SymbolCollection([]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([])
        );

        $projectionQuery = $this->createMock(Query::class);
        $projectionQuery->expects($this->once())->method('init')->willReturn($projectionQuery);
        $projectionQuery
            ->expects($this->once())
            ->method('fromStream')
            ->with(Exchange::class . '-' . $exchangeId)
            ->willReturn($projectionQuery);
        $projectionQuery->expects($this->once())->method('whenAny')->willReturn($projectionQuery);
        $projectionQuery
            ->expects($this->once())
            ->method('run');
        $projectionQuery
            ->expects($this->once())
            ->method('getState')
        ->willReturn([
            new ExchangeCreated($exchange)
        ]);

        $projectionManager = $this->createMock(ProjectionManager::class);
        $projectionManager
            ->expects($this->once())
            ->method('createQuery')
            ->willReturn($projectionQuery);

        $getExchangeByIdQuery = new GetExchangeByIdQuery($exchangeId);
        $getExchangeByIdHandler = new GetExchangeByIdHandler($projectionManager);
        $retrievedExchange = $getExchangeByIdHandler($getExchangeByIdQuery);

        $this->assertInstanceOf(Exchange::class, $retrievedExchange);
        $this->assertEquals($exchangeId->toString(), $retrievedExchange->id()->toString());
    }
}
