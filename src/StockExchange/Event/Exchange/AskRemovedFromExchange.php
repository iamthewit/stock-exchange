<?php

namespace StockExchange\StockExchange\Event\Exchange;

use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\Event\Event;

class AskRemovedFromExchange extends Event
{
    private Ask $ask;

    /**
     * RemoveAskFromExchange constructor.
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
