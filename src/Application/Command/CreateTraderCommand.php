<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exchange;

class CreateTraderCommand
{
    private UuidInterface $exchangeId;
    private UuidInterface $traderId;

    /**
     * @param UuidInterface $exchangeId
     * @param UuidInterface $traderId
     */
    public function __construct(UuidInterface $exchangeId, UuidInterface $traderId)
    {
        $this->exchangeId = $exchangeId;
        $this->traderId = $traderId;
    }

    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
    }
}
