<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\TradeCollectionCreationException;

class TradeCollection implements IteratorAggregate, Countable, JsonSerializable
{
    private array $trades;

    /**
     * Images constructor.
     * @param array $trades
     * @throws TradeCollectionCreationException
     */
    public function __construct(array $trades)
    {
        $this->trades = [];

        foreach ($trades as $trade) {
            if (!is_a($trade, Trade::class)) {
                throw new TradeCollectionCreationException(
                    'Can only create a TradeCollection from an array of Trade objects.'
                );
            }

            $this->trades[] = $trade;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->trades;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->trades);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->trades);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
