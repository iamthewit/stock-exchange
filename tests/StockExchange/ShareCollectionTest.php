<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\ShareCollection;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;

class ShareCollectionTest extends TestCase
{
    /**
     * @dataProvider notShareProvider
     */
    public function testItThrowsShareCollectionCreationException(array $notShares)
    {
        $this->expectException(ShareCollectionCreationException::class);
        $this->expectExceptionMessage('Can only create a ShareCollection from an array of Share objects.');

        new ShareCollection($notShares);
    }

    public function testToArray()
    {
        $share1 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $share2 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $collection = new ShareCollection([$share1, $share2]);
        $this->assertEquals(
            [
                $share1->id()->toString() => $share1,
                $share2->id()->toString() => $share2,
            ],
            $collection->toArray()
        );
    }

    public function testJsonserialise()
    {
        $share1 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $share2 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $collection = new ShareCollection([$share1, $share2]);
        $this->assertEquals(
            [
                $share1->id()->toString() => $share1,
                $share2->id()->toString() => $share2,
            ],
            $collection->jsonSerialize()
        );
    }

    public function testJsonEncode()
    {
        $share1 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $share2 = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );
        $collection = new ShareCollection([$share1, $share2]);

        $this->assertEquals(
            json_encode([
                $share1->id()->toString() => [
                    'id' => $share1->id()->toString(),
                    'symbol' => $share1->symbol()->value(),
                    'owner_id' => null
                ],
                $share2->id()->toString() => [
                    'id' => $share2->id()->toString(),
                    'symbol' => $share2->symbol()->value(),
                    'owner_id' => null
                ],
            ]),
            json_encode($collection)
        );
    }

    public function testGetIterator()
    {
        $collection = new ShareCollection(
            [
                Share::create(
                    Uuid::uuid4(),
                    Symbol::fromValue('BAR')
                )
            ]
        );

        $this->assertIsIterable($collection);
        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
    }

    public function testCount()
    {
        $collection = new ShareCollection([
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            )
        ]);

        $this->assertEquals(2, $collection->count());
        $this->assertCount(2, $collection);
    }

    public function testItFiltersBySymbol()
    {
        $collection = new ShareCollection([
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('BAR')
            ),
            Share::create(
                Uuid::uuid4(),
                Symbol::fromValue('BAR')
            ),
        ]);

        $filteredCollection = $collection->filterBySymbol(
            Symbol::fromValue('FOO'),
        );

        $this->assertCount(2, $filteredCollection);
        $this->assertInstanceOf(ShareCollection::class, $filteredCollection);
    }

    public function testFilterByOwnerId()
    {
        $share1 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share2 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share3 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share4 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $trader1 = Trader::create(Uuid::uuid4());
        $trader2 = Trader::create(Uuid::uuid4());

        $share1->transferOwnershipToTrader($trader1);
        $share2->transferOwnershipToTrader($trader1);
        $share3->transferOwnershipToTrader($trader1);
        $share4->transferOwnershipToTrader($trader2);

        $collection = new ShareCollection([$share1, $share2, $share3, $share4]);

        $filteredCollection = $collection->filterByOwnerId($trader1->id());

        $this->assertCount(3, $filteredCollection);
        $this->assertInstanceOf(ShareCollection::class, $filteredCollection);
    }

    public function testRemoveShare()
    {
        $share1 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share2 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share3 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share4 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $collection = new ShareCollection([$share1, $share2, $share3, $share4]);

        $collection = $collection->removeShare($share4->id());

        $this->assertInstanceOf(ShareCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertNull($collection->findById($share4->id()));
    }

    public function testMatch()
    {
        $share1 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share2 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $collection = new ShareCollection([$share1]);

        $this->assertTrue($collection->match($share1));
        $this->assertFalse($collection->match($share2));
    }

    public function testFindByIdReturnsNull()
    {
        $collection = new ShareCollection([
            Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'))
        ]);

        $this->assertNull($collection->findById(Uuid::uuid4()));
    }

    public function testFindByIdReturnsShare()
    {
        $share1 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share2 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share3 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));
        $share4 = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $collection = new ShareCollection([$share1, $share2, $share3, $share4]);

        $foundShare = $collection->findById($share3->id());

        $this->assertInstanceOf(Share::class, $foundShare);
        $this->assertEquals($share3->id()->toString(), $foundShare->id()->toString());
    }

    private function notShareProvider(): array
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
