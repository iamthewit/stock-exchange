<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exchange;

class CreateTraderCommand
{
    private Exchange $exchange;
    private UuidInterface $traderId;

    /**
     * CreateTraderCommand constructor.
     *
     * @param Exchange $exchange * @param UuidInterface $id
     */
    public function __construct(Exchange $exchange, UuidInterface $traderId)
    {
        $this->exchange = $exchange;
        $this->traderId = $traderId;
    }

    public function exchange(): Exchange
    {
        return $this->exchange;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
    }
}
