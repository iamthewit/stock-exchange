<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Bid;

class BidCreated implements EventInterface
{
    /**
     * BidCreated constructor.
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
    }
}