<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use stdClass;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\Exception\TradeCollectionCreationException;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trade;
use StockExchange\StockExchange\TradeCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Trader;

class TradeCollectionTest extends TestCase
{
    /**
     * @dataProvider notTradesDataProvider
     */
    public function testItThrowsTradeCollectionCreationException(array $notTrades)
    {
        $this->expectException(TradeCollectionCreationException::class);
        $this->expectExceptionMessage('Can only create a TradeCollection from an array of Trade objects.');

        new TradeCollection($notTrades);
    }

    public function testToArray()
    {
        $trade = Trade::fromBidAndAsk(
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
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $collection = new TradeCollection([$trade]);

        $this->assertIsArray($collection->toArray());
        $this->assertEquals([$trade], $collection->toArray());
    }

    public function testJsonSerialize()
    {
        $trade = Trade::fromBidAndAsk(
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
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $collection = new TradeCollection([$trade]);

        $this->assertEquals([$trade], $collection->jsonSerialize());
    }

    public function testJsonEncode()
    {
        $trade = Trade::fromBidAndAsk(
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
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $collection = new TradeCollection([$trade]);

        $this->assertEquals(
            json_encode([
                [
                    'id' => $trade->id()->toString(),
                    'bid' => [
                        'id' => $trade->bid()->id()->toString(),
                        'trader' => [
                            'id' => $trade->bid()->trader()->id()->toString(),
                            'shares' => []
                        ],
                        'symbol' => [
                            'value' => 'FOO',
                        ],
                        'price' => [
                            'value' => 100,
                        ]
                    ],
                    'ask' => [
                        'id' => $trade->ask()->id()->toString(),
                        'trader' => [
                            'id' => $trade->ask()->trader()->id()->toString(),
                            'shares' => []
                        ],
                        'symbol' => [
                            'value' => 'FOO',
                        ],
                        'price' => [
                            'value' => 100,
                        ]
                    ]
                ]
            ]),
            json_encode($collection)
        );
    }

    public function testGetIterator()
    {
        $trade = Trade::fromBidAndAsk(
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
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $collection = new TradeCollection([$trade]);

        $this->assertIsIterable($collection);
        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
    }

    public function testCount()
    {
        $trade = Trade::fromBidAndAsk(
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
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $collection = new TradeCollection([$trade, $trade]);

        $this->assertEquals(2, $collection->count());
        $this->assertCount(2, $collection);
    }

    private function notTradesDataProvider()
    {
        return [
            [[1]],
            [[1.1]],
            [[true]],
            [['string']],
            [[new stdClass()]],
        ];
    }
}
