<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\SymbolCollectionCreationException;

class SymbolCollection implements IteratorAggregate, Countable, JsonSerializable
{
    private array $symbols;

    /**
     * SymbolCollection constructor.
     * @param array $symbols
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
     * @return ArrayIterator
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
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->symbols;
    }
}