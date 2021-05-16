<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Bid;

class BidRemovedFromExchange implements EventInterface
{
    private Bid $bid;

    /**
     * RemoveBidFromExchange constructor.
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
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