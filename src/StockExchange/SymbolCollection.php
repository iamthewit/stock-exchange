<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\SymbolCollectionCreationException;

/**
 * Class SymbolCollection
 * @package StockExchange\StockExchange
 *
 * @implements IteratorAggregate<int, Symbol>
 */
class SymbolCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var array<int, Symbol>
     */
    private array $symbols;

    /**
     * SymbolCollection constructor.
     * @param array<int, Symbol> $symbols
     * @throws SymbolCollectionCreationException
     */
    public function __construct(array $symbols)
    {
        $this->symbols = [];

        foreach ($symbols as $symbol) {
            if (!is_a($symbol, Symbol::class)) {
                throw new SymbolCollectionCreationException(
                    'Can only create a SymbolCollection from an array of Symbol objects.'
                );
            }

            $this->symbols[] = $symbol;
        }
    }

    /**
     * @return ArrayIterator<int, Symbol>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->symbols);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->symbols);
    }

    /**
     * @return array<int, Symbol>
     */
    public function jsonSerialize(): array
    {
        return $this->symbols;
    }
}
