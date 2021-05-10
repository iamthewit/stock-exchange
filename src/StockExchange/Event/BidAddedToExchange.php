<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Bid;

class BidAddedToExchange implements EventInterface
{
    /**
     * BidAdded constructor.
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
    }
}