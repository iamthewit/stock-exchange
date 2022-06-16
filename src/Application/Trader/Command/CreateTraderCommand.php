<?php

namespace StockExchange\Application\Trader\Command;

use Ramsey\Uuid\UuidInterface;

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
