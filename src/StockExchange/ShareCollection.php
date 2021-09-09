<?php

namespace StockExchange\StockExchange;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;

/**
 * Class ShareCollection
 * @package StockExchange\StockExchange
 *
 * @implements IteratorAggregate<string, Share>
 */
class ShareCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * @var Share[]
     */
    private array $shares;

    /**
     * Images constructor.
     * @param Share[] $shares
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
     * @return Share[]
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
     * @return Share[]
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param Symbol $symbol
     *
     * @return ShareCollection
     * @throws ShareCollectionCreationException
     */
    public function filterBySymbol(Symbol $symbol): ShareCollection
    {
        return new self(
            array_filter($this->shares, function (Share $share) use ($symbol) {
                return $share->symbol()->value() === $symbol->value();
            })
        );
    }

    public function filterByOwnerId(UuidInterface $ownerId): ShareCollection
    {
        return new self(
            array_filter($this->shares, function (Share $share) use ($ownerId) {
                return $share->ownerId()->equals($ownerId);
            })
        );
    }

    public function removeShare(UuidInterface $id): ShareCollection
    {
        $shares = $this->shares;
        unset($shares[$id->toString()]);

        return new self($shares);
    }

    public function match(Share $share): bool
    {
        if (array_key_exists($share->id()->toString(), $this->toArray())) {
            return true;
        }

        return false;
    }
}
