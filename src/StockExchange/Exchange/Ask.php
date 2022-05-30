<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Exchange;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class Ask implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private UuidInterface $traderId;
    private Symbol $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Ask
     */
    public static function create(
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): Ask {
        $ask = new self();
        $ask->id = $id;
        $ask->traderId = $traderId;
        $ask->symbol = $symbol;
        $ask->price = $price;

        return $ask;
    }

    public static function restoreFromValues(
        UuidInterface $id,
        UuidInterface $trader,
        Symbol $symbol,
        Price $price
    ): Ask {
        $ask = new self();
        $ask->id = $id;
        $ask->traderId = $trader;
        $ask->symbol = $symbol;
        $ask->price = $price;

        return $ask;
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
            'id' => $this->id()->toString(),
            'traderId' => $this->traderId()->toString(),
            'symbol' => $this->symbol()->toArray(),
            'price' => $this->price()->toArray()
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
