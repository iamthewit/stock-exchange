<?php

namespace StockExchange\StockExchange\Exchange\Event;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exchange\Ask;

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
