<?php

namespace StockExchange\Application\Query;

use StockExchange\StockExchange\Exchange;

class GetTradesQuery
{
    private Exchange $exchange;

    /**
     * GetTradesQuery constructor.
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
