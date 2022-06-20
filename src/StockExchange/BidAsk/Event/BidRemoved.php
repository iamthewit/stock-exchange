<?php

namespace StockExchange\StockExchange\BidAsk\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Event\Event;

class BidRemoved extends Event
{
    private UuidInterface $bidId;

    /**
     * BidAdded constructor.
     *
     * @param UuidInterface $bidId
     */
    public function __construct(UuidInterface $bidId)
    {
        $this->init();
        $this->setPayload(['id' => $bidId]);
        $this->bidId = $bidId;
    }

    /**
     * @return UuidInterface
     */
    public function bidId(): UuidInterface
    {
        return $this->bidId;
    }
}
