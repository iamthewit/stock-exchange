<?php

namespace StockExchange\Application\BidAsk\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class RemoveAskCommand
{
    private UuidInterface $exchangeId;
    private UuidInterface $id;

    /**
     * CreateAskCommand constructor.
     *
     * @param UuidInterface $exchangeId
     * @param UuidInterface $id
     */
    public function __construct(
        UuidInterface $exchangeId,
        UuidInterface $id
    ) {
        $this->exchangeId = $exchangeId;
        $this->id = $id;
    }

    /**
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }
}
