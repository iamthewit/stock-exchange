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

    // we should probably store the exchange id against the trader too
    // at the moment only one exchange exists, though the exchange has an ID
    // and it is not impossible to imagine that multiple exchanges might
    // exist in the future

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
        $trader = new self();
        $trader->id = $id;
        $trader->shares = new ShareCollection([]);

        return $trader;
    }

    /**
     * @param UuidInterface $id
     * @param ShareCollection $shares
     * @return Trader
     */
    public static function restoreFromValues(UuidInterface $id, ShareCollection $shares): Trader
    {
        $trader = new self();
        $trader->id = $id;
        $trader->shares = $shares;

        return $trader;
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
    }

    /**
     * @return array<string, ShareCollection|string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'shares' => $this->shares()->toArray()
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
