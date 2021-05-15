<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Ask;

class AskAddedToExchange implements EventInterface
{
    private Ask $ask;

    /**
     * AskAdded constructor.
     * @param Ask $ask
     */
    public function __construct(Ask $ask)
    {
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