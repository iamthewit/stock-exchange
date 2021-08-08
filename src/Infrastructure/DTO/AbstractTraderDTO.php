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

    abstract public function asArray(): array;

    public function jsonSerialize(): array
    {
        return $this->asArray();
    }
}
