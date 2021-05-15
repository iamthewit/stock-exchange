<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Exchange;

class ExchangeCreated implements EventInterface
{
    private Exchange $exchange;

    /**
     * ExchangeCreated constructor.
     * @param Exchange $exchange
     */
    public function __construct(Exchange $exchange)
    {
        $this->exchange = $exchange;
    }

    /**
     * @return Exchange
     */
    public function exchange(): Exchange
    {
        return $this->exchange;
    }
}