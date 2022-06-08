<?php

namespace StockExchange\StockExchange\BidAsk\Event;

use StockExchange\StockExchange\BidAsk\Ask;
use StockExchange\StockExchange\Event\Event;

class AskRemoved extends Event
{
    private Ask $ask;

    /**
     * AskAdded constructor.
     * @param Ask $ask
     */
    public function __construct(Ask $ask)
    {
        $this->init();
        $this->setPayload($ask->toArray());
        $this->ask = $ask;
    }

    /**
     * @return Ask
     */
    public function ask(): Ask
    {
        return $this->ask;
    }
}
