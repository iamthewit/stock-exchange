<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Symbol;

class CreateShareCommand
{
    private Exchange $exchange;
    private UuidInterface $shareId;
    private Symbol $symbol;

    /**
     * CreateShareCommand constructor.
     *
     * @param Exchange      $exchange
     * @param UuidInterface $shareId
     * @param Symbol        $symbol
     */
    public function __construct(Exchange $exchange, UuidInterface $shareId, Symbol $symbol)
    {
        $this->exchange = $exchange;
        $this->shareId = $shareId;
        $this->symbol  = $symbol;
    }

    public function exchange(): Exchange
    {
        return $this->exchange;
    }

    public function shareId(): UuidInterface
    {
        return $this->shareId;
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }
}
