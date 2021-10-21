<?php

namespace StockExchange\Application\Query;

use Ramsey\Uuid\UuidInterface;

class GetTraderByIdQuery
{
    private UuidInterface $id;
    private UuidInterface $exchangeId;

    /**
     * GetTraderByIdQuery constructor.
     *
     * @param UuidInterface $id
     * @param UuidInterface $exchangeId
     */
    public function __construct(UuidInterface $id, UuidInterface $exchangeId)
    {
        $this->id = $id;
        $this->exchangeId = $exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }
}
