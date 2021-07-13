<?php

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;

/**
 * Class Trader
 * @package StockExchange\StockExchange
 */
class Trader implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private ShareCollection $shares;

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     *
     * @return self
     * @throws Exception\ShareCollectionCreationException
     */
    public static function create(UuidInterface $id): self
    {
        $seller = new self();
        $seller->id = $id;
        $seller->shares = new ShareCollection([]);

        return $seller;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return ShareCollection
     */
    public function shares(): ShareCollection
    {
        return $this->shares;
    }

    /**
     * @param Share $share
     *
     * @throws Exception\ShareCollectionCreationException
     */
    public function addShare(Share $share): void
    {
        $this->shares = new ShareCollection($this->shares->toArray() + [$share]);

        // TODO: emit share added event
    }

    /**
     * @param Share $share
     *
     * @throws Exception\ShareCollectionCreationException
     */
    public function removeShare(Share $share): void
    {
        $shares = $this->shares()->toArray();
        unset($shares[$share->id()->toString()]);

        $this->shares = new ShareCollection($shares);

        // TODO: emit share removed event
    }

    /**
     * @return array<string, ShareCollection|string>
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'shares' => $this->shares()
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }
}
