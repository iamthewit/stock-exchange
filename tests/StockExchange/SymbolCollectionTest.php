<?php

namespace StockExchange\Tests\StockExchange;

use StockExchange\StockExchange\Exception\SymbolCollectionCreationException;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\SymbolCollection;
use PHPUnit\Framework\TestCase;

class SymbolCollectionTest extends TestCase
{
    /**
     * @dataProvider notSymbolProvider
     */
    public function testItThrowsSymbolCollectionCreationException(array $notSymbols)
    {
        $this->expectException(SymbolCollectionCreationException::class);
        $this->expectExceptionMessage('Can only create a SymbolCollection from an array of Symbol objects.');

        new SymbolCollection($notSymbols);
    }

    public function testGetIterator()
    {
        $collection = new SymbolCollection([Symbol::fromValue('FOO')]);

        $this->assertIsIterable($collection);
        $this->assertInstanceOf(\ArrayIterator::class, $collection->getIterator());
    }

    public function testCount()
    {
        $collection = new SymbolCollection([
            Symbol::fromValue('FOO'),
            Symbol::fromValue('BAR'),
            Symbol::fromValue('BAZ'),
        ]);

        $this->assertEquals(3, $collection->count());
        $this->assertCount(3, $collection);
    }

    public function testToArray()
    {
        $symbolFoo = Symbol::fromValue('FOO');
        $symbolBar = Symbol::fromValue('BAR');
        $collection = new SymbolCollection([$symbolFoo, $symbolBar]);

        $this->assertEquals([
            $symbolFoo,
            $symbolBar
        ], $collection->toArray());
    }

    public function testJsonSerialise()
    {
        $symbolFoo = Symbol::fromValue('FOO');
        $symbolBar = Symbol::fromValue('BAR');
        $collection = new SymbolCollection([$symbolFoo, $symbolBar]);

        $this->assertEquals([
            $symbolFoo,
            $symbolBar
        ], $collection->toArray());
    }

    public function testJsonEncode()
    {
        $symbolFoo = Symbol::fromValue('FOO');
        $symbolBar = Symbol::fromValue('BAR');
        $collection = new SymbolCollection([$symbolFoo, $symbolBar]);

        $this->assertEquals(
            json_encode([
                ['value' => 'FOO'],
                ['value' => 'BAR'],
            ]),
            json_encode($collection)
        );
    }

    private function notSymbolProvider(): array
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
