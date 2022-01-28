<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\AskCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Exception\AskCollectionCreationException;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class AskCollectionTest extends TestCase
{
    /**
     * @dataProvider notAskProvider
     */
    public function testItThrowsAskCollectionCreationException(array $notAsks)
    {
        $this->expectException(AskCollectionCreationException::class);
        $this->expectExceptionMessage('Can only create a AskCollection from an array of Ask objects.');

        new AskCollection($notAsks);
    }

    public function testGetIterator()
    {
        $collection = new AskCollection(
            [
                Ask::create(
                    Uuid::uuid4(),
                    Trader::create(Uuid::uuid4()),
                    Symbol::fromValue('FOO'),
                    Price::fromValue(100)
                )
            ]
        );

        $this->assertIsIterable($collection);
        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
    }

    public function testToArray()
    {
        $ask1 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new AskCollection([$ask1, $ask2]);

        $this->assertEquals([
            $ask1->id()->toString() => $ask1,
            $ask2->id()->toString() => $ask2
        ], $collection->toArray());
    }

    public function testJsonSerialise()
    {
        $ask1 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new AskCollection([$ask1, $ask2]);

        $this->assertEquals([
            $ask1->id()->toString() => $ask1,
            $ask2->id()->toString() => $ask2
        ], $collection->jsonSerialize());
    }

    public function testJsonEncode()
    {
        $ask1 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new AskCollection([$ask1, $ask2]);

        $this->assertEquals(
            json_encode([
                $ask1->id()->toString() => [
                    'id' => $ask1->id()->toString(),
                    'trader' => [
                        'id' => $ask1->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ],
                $ask2->id()->toString() =>[
                    'id' => $ask2->id()->toString(),
                    'trader' => [
                        'id' => $ask2->trader()->id()->toString(),
                        'shares' => []
                    ],
                    'symbol' => [
                        'value' => 'FOO'
                    ],
                    'price' => [
                        'value' => 100
                    ]
                ],
            ]),
            json_encode($collection)
        );
    }

    public function testFilterBySymbolAndPrice()
    {
        $collection = new AskCollection([
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(200)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Ask::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
        ]);

        $filteredCollection = $collection->filterBySymbolAndPrice(
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        $this->assertCount(2, $filteredCollection);
    }

    public function testFindById()
    {
        $ask1 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(200)
        );
        $ask3 = Ask::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('BAR'),
            Price::fromValue(100)
        );
        $collection = new AskCollection([
            $ask1,
            $ask2,
            $ask3,
        ]);

        $this->assertEquals($ask2, $collection->findById($ask2->id()));
    }

    private function notAskProvider(): array
    {
        return [
            [[1]],
            [[1.1]],
            [['one']],
            [[true]],
            [[new \stdClass()]]
        ];
    }
}
