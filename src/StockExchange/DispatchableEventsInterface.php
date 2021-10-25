<?php

namespace StockExchange\StockExchange;

use StockExchange\StockExchange\Event\Event;

interface DispatchableEventsInterface
{
    /**
     * @return Event[]
     */
    public function dispatchableEvents(): array;
}
