<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exception\AskCollectionCreationException;

/**
 * Class AskCollection
 * @package StockExchange\StockExchange
 *
 * @implements IteratorAggregate<string, Ask>
 */
class AskCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var Ask[]
     */
    private array $asks;

    /**
     * Images constructor.
     * @param Ask[] $asks
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
     * @return Ask[]
     */
    public function toArray(): array
    {
        return $this->asks;
    }

    /**
     * @return ArrayIterator<string, Ask>
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
     * @return Ask[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param Symbol $symbol
     * @param Price  $price
     *
     * @return AskCollection
     * @throws AskCollectionCreationException
     */
    public function filterBySymbolAndPrice(Symbol $symbol, Price $price): AskCollection
    {
        return new self(
            array_filter($this->asks, function (Ask $ask) use ($symbol, $price) {
                    return $ask->symbol()->value() === $symbol->value()
                        && $ask->price()->value() === $price->value();
            })
        );
    }

    public function findById(UuidInterface $id): Ask
    {
        return $this->toArray()[$id->toString()];
    }
}
