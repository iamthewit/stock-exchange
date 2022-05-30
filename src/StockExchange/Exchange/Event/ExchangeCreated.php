<?php


namespace StockExchange\StockExchange\Exchange\Event;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exchange\Exchange;

/**
 * Class ExchangeCreated
 * @package StockExchange\StockExchange\Exchange\Event
 */
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