<?php

namespace StockExchange\StockExchange;

use StockExchange\StockExchange\Event\EventInterface;

interface DispatchableEventsInterface
{
    /**
     * @return EventInterface[]
     */
    public function dispatchableEvents(): array;
}
