<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;

class CreateExchangeCommand
{
    private UuidInterface $id;

    /**
     * CreateExchangeCommand constructor.
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
