<?php

namespace StockExchange\Infrastructure\DTO;

use StockExchange\StockExchange\Trader;

abstract class AbstractTraderDTO implements \JsonSerializable
{
    private Trader $trader;

    /**
     * TraderDTO constructor.
     *
     * @param Trader $trader
     */
    public function __construct(Trader $trader)
    {
        $this->trader = $trader;
    }

    /**
     * @return Trader
     */
    public function getTrader(): Trader
    {
        return $this->trader;
    }

    public function asArray(): array
    {
        return $this->trader->asArray();
    }

    abstract public function jsonSerialize(): array;
}
