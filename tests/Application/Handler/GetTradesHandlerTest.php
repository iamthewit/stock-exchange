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
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\Trade;
use StockExchange\StockExchange\TradeCollection;
use StockExchange\StockExchange\Trader;

class GetTradesHandlerTest extends TestCase
{
    public function testItReturnsATradeCollection()
    {
        $query = new GetTradesQuery(
            Exchange::create(
                Uuid::uuid4(),
                new SymbolCollection([]),
                new BidCollection([]),
                new AskCollection([]),
                new TradeCollection([
                    Trade::fromBidAndAsk(
                        Uuid::uuid4(),
                        Bid::create(
                            Uuid::uuid4(),
                            Trader::create(Uuid::uuid4()),
                            Symbol::fromValue('FOO'),
                            Price::fromValue(100)
                        ),
                        Ask::create(
                            Uuid::uuid4(),
                            Trader::create(Uuid::uuid4()),
                            Symbol::fromValue('BAR'),
                            Price::fromValue(100)
                        )
                    )
                ])
            )
        );

        $handler = new GetTradesHandler();
        $trades = $handler($query);

        $this->assertInstanceOf(TradeCollection::class, $trades);
    }
}
