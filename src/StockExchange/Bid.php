<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;

class Bid implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private Trader $trader;
    private Symbol $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * TODO: is issue a better name? are bids issued rather than created?
     *
     * @param UuidInterface $id
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Bid
     */
    public static function create(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): self {
        $bid = new self();
        $bid->id = $id;
        $bid->trader = $trader;
        $bid->symbol = $symbol;
        $bid->price = $price;

        return $bid;
    }

    public static function restoreFromValues(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): Bid {
        $bid = new self();
        $bid->id = $id;
        $bid->trader = $trader;
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
     * @return Trader
     */
    public function trader(): Trader
    {
        return $this->trader;
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
     * @return array{id: string, trader: Trader, symbol: Symbol, price: Price}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'trader' => $this->trader()->toArray(),
            'symbol' => $this->symbol()->toArray(),
            'price' => $this->price()->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
