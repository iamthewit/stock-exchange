<?php

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exception\TraderCollectionCreationException;

/**
 * Class TraderCollection
 * @package StockExchange\StockExchange
 */
class TraderCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /** @var array<int, Trader> */
    private array $traders;

    /**
     * Images constructor.
     * @param array<int, Trader> $traders
     * @throws TraderCollectionCreationException
     */
    public function __construct(array $traders)
    {
        $this->traders = [];

        foreach ($traders as $trader) {
            if (!is_a($trader, Trader::class)) {
                throw new TraderCollectionCreationException(
                    'Can only create a TraderCollection from an array of Trader objects.'
                );
            }

            $this->traders[$trader->id()->toString()] = $trader;
        }
    }

    /**
     * @return array<int, Trader>
     */
    public function toArray(): array
    {
        return $this->traders;
    }

    /**
     * @return ArrayIterator<int, Trader>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->traders);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->traders);
    }

    /**
     * @return array<int, Trader>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function removeTrader(UuidInterface $id): TraderCollection
    {
        $traders = $this->traders;
        unset($traders[$id->toString()]);

        return new self($traders);
    }

    /**
     * @param UuidInterface $id
     *
     * @return Trader
     */
    public function findById(UuidInterface $id): Trader
    {
        // TODO: throw exception if the array key does not exist
        return $this->toArray()[$id->toString()];
    }

    public function match(Trader $trader): bool
    {
        if (array_key_exists($trader->id()->toString(), $this->toArray())) {
            return true;
        }

        return false;
    }
}
