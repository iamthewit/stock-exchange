<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trade;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Trader;

class TradeTest extends TestCase
{
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

        $this->assertEquals(
            [
                'id' => $trade->id()->toString(),
                'bid' => [
                    'id' => $trade->bid()->id()->toString(),
                    'trader' => [
                        'id' => $trade->bid()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ],
                'ask' => [
                    'id' => $trade->ask()->id()->toString(),
                    'trader' => [
                        'id' => $trade->ask()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ]
            ],
            $trade->toArray()
        );
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

        $this->assertEquals(
            [
                'id' => $trade->id()->toString(),
                'bid' => [
                    'id' => $trade->bid()->id()->toString(),
                    'trader' => [
                        'id' => $trade->bid()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ],
                'ask' => [
                    'id' => $trade->ask()->id()->toString(),
                    'trader' => [
                        'id' => $trade->ask()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ]
            ],
            $trade->jsonSerialize()
        );
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

        $this->assertEquals(
            json_encode([
                'id' => $trade->id()->toString(),
                'bid' => [
                    'id' => $trade->bid()->id()->toString(),
                    'trader' => [
                        'id' => $trade->bid()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ],
                'ask' => [
                    'id' => $trade->ask()->id()->toString(),
                    'trader' => [
                        'id' => $trade->ask()->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ]
            ]),
            json_encode($trade)
        );
    }
}
