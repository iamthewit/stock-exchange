<?php

namespace StockExchange\StockExchange\BidAsk\Event;

use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Event\Event;

class BidRemoved extends Event
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
