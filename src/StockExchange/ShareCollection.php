<?php

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;

class ShareCollection implements IteratorAggregate, Countable, JsonSerializable
{
    private array $shares;

    /**
     * Images constructor.
     * @param array $shares
     * @throws ShareCollectionCreationException
     */
    public function __construct(array $shares)
    {
        $this->shares = [];

        foreach ($shares as $share) {
            if (!is_a($share, Share::class)) {
                throw new ShareCollectionCreationException(
                    'Can only create a ShareCollection from an array of Share objects.'
                );
            }

            $this->shares[$share->id()->toString()] = $share;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->shares;
    }

    /**
     * @return ArrayIterator<string, Share>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->shares);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->shares);
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
     *
     * @return $this
     * @throws ShareCollectionCreationException
     */
    public function filterBySymbol(Symbol $symbol): self
    {
        return new self(
            array_filter($this->shares, function (Share $share) use ($symbol) {
                return $share->symbol()->value() === $symbol->value();
            })
        );
    }
}
