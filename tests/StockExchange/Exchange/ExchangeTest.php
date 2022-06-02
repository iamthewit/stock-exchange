<?php

namespace StockExchange\Tests\StockExchange\Exchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Exchange\Event\ExchangeCreated;
use StockExchange\StockExchange\Exchange\Exchange;
use PHPUnit\Framework\TestCase;

class ExchangeTest extends TestCase
{
    public function testCreateReturnsExchange()
    {
        $this->assertInstanceOf(Exchange::class, Exchange::create(Uuid::uuid4()));
    }

    public function testCreateAddsExchangeCreatedDispatchableEvent()
    {
        $exchange = Exchange::create(Uuid::uuid4());
        $dispatchableEvent = $exchange->dispatchableEvents()[0];

        $this->assertInstanceOf(ExchangeCreated::class, $dispatchableEvent);
    }

    public function testAsk()
    {

    }

    public function testBid()
    {

    }

    public function testTradeIsExecuted()
    {

    }
}
