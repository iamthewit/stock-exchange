<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Symbol;

class CreateShareCommand
{
    private UuidInterface $id;
    private Symbol $symbol;

    /**
     * CreateShareCommand constructor.
     * @param UuidInterface $id
     * @param Symbol $symbol
     */
    public function __construct(UuidInterface $id, Symbol $symbol)
    {
        $this->id = $id;
        $this->symbol = $symbol;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }
}
