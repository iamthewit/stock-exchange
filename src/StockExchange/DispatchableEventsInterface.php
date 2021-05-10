<?php

namespace StockExchange\StockExchange;

interface DispatchableEventsInterface
{
    public function dispatchableEvents(): array;
}