<?php

namespace StockExchange\StockExchange\Exchange\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exchange\Bid;

class BidRemovedFromExchange extends Event
{
    private UuidInterface $bidId;

    /**
     * RemoveBidFromExchange constructor.
     *
     * @param UuidInterface $bidId
     */
    public function __construct(UuidInterface $bidId)
    {
        $this->init();
        $this->setPayload(['bidId' => $bidId->toString()]);
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
