<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Exchange;

class ExchangeCreated implements EventInterface
{

    /**
     * ExchangeCreated constructor.
     * @param Exchange $exchange
     */
    public function __construct(Exchange $exchange)
    {
    }
}