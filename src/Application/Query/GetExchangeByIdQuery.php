<?php

namespace StockExchange\Application\Query;

use Ramsey\Uuid\UuidInterface;

class GetExchangeByIdQuery
{
    private UuidInterface $id;

    /**
     * GetExchangeQuery constructor.
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