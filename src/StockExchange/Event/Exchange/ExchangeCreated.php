<?php

namespace StockExchange\StockExchange\Event\Exchange;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exchange;

class ExchangeCreated extends Event
{
    private Exchange $exchange;

    /**
     * ExchangeCreated constructor.
     * @param Exchange $exchange
     */
    public function __construct(Exchange $exchange)
    {
        $this->init();
        $this->setPayload($exchange->toArray());
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
