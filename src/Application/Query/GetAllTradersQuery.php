<?php

namespace StockExchange\Application\Query;

use Ramsey\Uuid\UuidInterface;

/**
 * Class GetAllTradersQuery
 *
 * @package StockExchange\Application\Query
 */
class GetAllTradersQuery
{
    private UuidInterface $exchangeId;

    /**
     * GetTraderByIdQuery constructor.
     *
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