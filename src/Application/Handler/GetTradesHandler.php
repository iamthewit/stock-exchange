<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetTradesQuery;
use StockExchange\StockExchange\TradeCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetTradesHandler implements MessageHandlerInterface
{
    public function __invoke(GetTradesQuery $query): TradeCollection
    {
        return $query->exchange()->trades();
    }
}