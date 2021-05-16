<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\Exchange;

class GetExchangeByIdHandler
{
    public function __invoke(GetExchangeByIdQuery $query)
    {
        // TODO:
        $events = $this->exchangeEventStream->getEvents();

        return Exchange::restoreStateFromEvents($events);
    }
}