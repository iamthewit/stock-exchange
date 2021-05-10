<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Ask;

class AskAddedToExchange implements EventInterface
{

    /**
     * AskAdded constructor.
     */
    public function __construct(Ask $ask)
    {
    }
}