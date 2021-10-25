<?php

namespace StockExchange\StockExchange\Event\Exchange;

use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\Event\Event;

class BidAddedToExchange extends Event
{
    private Bid $bid;

    /**
     * BidAdded constructor.
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
        $this->init();
        $this->setPayload($bid->toArray());
        $this->bid = $bid;
    }

    /**
     * @return Bid
     */
    public function bid(): Bid
    {
        return $this->bid;
    }
}
