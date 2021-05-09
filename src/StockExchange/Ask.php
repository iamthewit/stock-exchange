<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;

class Ask
{
    private UuidInterface $id;
    private Trader        $trader;
    private Symbol        $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return static
     */
    public static function create(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): self
    {
        $ask = new self();
        $ask->id = $id;
        $ask->trader = $trader;
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

}