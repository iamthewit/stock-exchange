<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Exchange;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class Bid implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private UuidInterface $traderId;
    private Symbol        $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * TODO: is issue a better name? are bids issued rather than created?
     *
     * @param UuidInterface $id
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Bid
     */
    public static function create(
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): self {
        $bid = new self();
        $bid->id = $id;
        $bid->traderId = $traderId;
        $bid->symbol = $symbol;
        $bid->price = $price;

        return $bid;
    }

    public static function restoreFromValues(
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): Bid {
        $bid = new self();
        $bid->id = $id;
        $bid->traderId = $traderId;
        $bid->symbol = $symbol;
        $bid->price = $price;

        return $bid;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return Price
     */
    public function price(): Price
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'bidId' => $this->id()->toString(),
            'traderId' => $this->traderId()->toString(),
            'symbol' => $this->symbol()->toArray(),
            'price' => $this->price()->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
