<?php

namespace StockExchange\Application\Query;

use Ramsey\Uuid\UuidInterface;

class GetTradesQuery
{
    private UuidInterface $exchangeId;

    /**
     * GetTradesQuery constructor.
     * @param UuidInterface $exchangeId
     */
    public function __construct(UuidInterface $exchangeId)
    {
        $this->exchangeId = $exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }
}
