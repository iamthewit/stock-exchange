<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;

class CreateTraderCommand
{
    private UuidInterface $id;

    /**
     * CreateTraderCommand constructor.
     * @param UuidInterface $id
     */
    public function __construct(UuidInterface $id)
    {
        $this->id = $id;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }
}
