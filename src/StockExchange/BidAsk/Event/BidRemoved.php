<?php

namespace StockExchange\StockExchange\BidAsk\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Event\Event;

class BidRemoved extends Event
{
    private UuidInterface $bidId;
    private UuidInterface $exchangeId;

    /**
     * BidAdded constructor.
     *
     * @param UuidInterface $bidId
     */
    public function __construct(UuidInterface $bidId, UuidInterface $exchangeId)
    {
        $this->init();
        $this->setPayload([
            'id' => $bidId,
            'exchangeId' => $exchangeId
        ]);
        $this->bidId = $bidId;
    }

    /**
     * @return UuidInterface
     */
    public function bidId(): UuidInterface
    {
        return $this->bidId;
    }

    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }
}
