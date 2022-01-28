<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Exception\BidCollectionCreationException;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Symbol;

class BidCollectionTest extends TestCase
{
    /**
     * @dataProvider notBidProvider
     */
    public function testItThrowsBidCollectionCreationException(array $notBids)
    {
        $this->expectException(BidCollectionCreationException::class);
        $this->expectExceptionMessage('Can only create a BidCollection from an array of Bid objects.');

        new BidCollection($notBids);
    }

    public function testGetIterator()
    {
        $collection = new BidCollection(
            [
                Bid::create(
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
        $ask1 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new BidCollection([$ask1, $ask2]);

        $this->assertEquals([
            $ask1->id()->toString() => $ask1,
            $ask2->id()->toString() => $ask2
        ], $collection->toArray());
    }

    public function testJsonSerialise()
    {
        $ask1 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new BidCollection([$ask1, $ask2]);

        $this->assertEquals([
            $ask1->id()->toString() => $ask1,
            $ask2->id()->toString() => $ask2
        ], $collection->jsonSerialize());
    }

    public function testJsonEncode()
    {
        $ask1 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $ask2 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $collection = new BidCollection([$ask1, $ask2]);

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
        $collection = new BidCollection([
            Bid::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Bid::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(200)
            ),
            Bid::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(100)
            ),
            Bid::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('BAR'),
                Price::fromValue(200)
            ),
            Bid::create(
                Uuid::uuid4(),
                Trader::create(Uuid::uuid4()),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            ),
            Bid::create(
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
        $bid1 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $bid2 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(200)
        );
        $bid3 = Bid::create(
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('BAR'),
            Price::fromValue(100)
        );
        $collection = new BidCollection([
            $bid1,
            $bid2,
            $bid3,
        ]);

        $this->assertEquals($bid2, $collection->findById($bid2->id()));
    }

    private function notBidProvider(): array
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
