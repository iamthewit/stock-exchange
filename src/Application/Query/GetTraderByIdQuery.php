<?php

namespace StockExchange\Application\Query;

use Ramsey\Uuid\UuidInterface;

class GetTraderByIdQuery
{
    private UuidInterface $id;

    /**
     * GetTraderByIdQuery constructor.
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
