<?php

namespace StockExchange\StockExchange\Event;

use StockExchange\StockExchange\Trade;

class TradeExecuted implements EventInterface
{
    private Trade $trade;
    /**
     * TradeExecuted constructor.
     * @param Trade $trade
     */
    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    /**
     * @return Trade
     */
    public function trade(): Trade
    {
        return $this->trade;
    }
}