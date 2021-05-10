<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Ask;

class AskCreated implements EventInterface
{

    /**
     * AskCreated constructor.
     */
    public function __construct(Ask $ask)
    {
    }
}