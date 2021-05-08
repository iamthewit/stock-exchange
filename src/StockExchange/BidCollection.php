<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use StockExchange\StockExchange\Exception\BidCollectionCreationException;

class BidCollection implements IteratorAggregate, Countable, JsonSerializable
{
    private array $bids;

    /**
     * Images constructor.
     * @param array $bids
     * @throws BidCollectionCreationException
     */
    public function __construct(array $bids)
    {
        $this->bids = [];

        foreach ($bids as $bid) {
            if (!is_a($bid, Bid::class)) {
                throw new BidCollectionCreationException(
                    'Can only create a BidCollection from an array of Bid objects.'
                );
            }

            $this->bids[$bid->id()->toString()] = $bid;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->bids;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->bids);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->bids);
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
     * @throws BidCollectionCreationException
     */
    public function filterBySymbolAndPrice(Symbol $symbol, Price $price): self
    {
        return new self(
            array_filter($this->bids, function(Bid $bid) use ($symbol, $price) {
                return $bid->symbol()->value() === $symbol->value()
                    && $bid->price()->value() === $price->value();
            })
        );
    }
}