<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Event\Exchange;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Trader;

class TraderAddedToExchange extends Event
{
    private Trader $trader;

    public function __construct(Trader $trader)
    {
        $this->init();
        $this->setPayload($trader->toArray());
        $this->trader = $trader;
    }

    public function trader(): Trader
    {
        return $this->trader;
    }
}
