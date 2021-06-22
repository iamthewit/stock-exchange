<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\AskCollectionCreationException;

class AskCollection implements IteratorAggregate, Countable, JsonSerializable
{
    private array $asks;

    /**
     * Images constructor.
     * @param array $asks
     * @throws AskCollectionCreationException
     */
    public function __construct(array $asks)
    {
        $this->asks = [];

        foreach ($asks as $ask) {
            if (!is_a($ask, Ask::class)) {
                throw new AskCollectionCreationException(
                    'Can only create a AskCollection from an array of Ask objects.'
                );
            }

            $this->asks[$ask->id()->toString()] = $ask;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->asks;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->asks);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->asks);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param Symbol $symbol
     * @param Price  $price
     *
     * @return $this
     * @throws AskCollectionCreationException
     */
    public function filterBySymbolAndPrice(Symbol $symbol, Price $price): self
    {
        return new self(
            array_filter($this->asks, function (Ask $ask) use ($symbol, $price) {
                    return $ask->symbol()->value() === $symbol->value()
                        && $ask->price()->value() === $price->value();
            })
        );
    }
}
