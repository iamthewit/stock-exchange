<?php

namespace StockExchange\StockExchange;

interface ExchangeWriteRepositoryInterface
{
    public function store(Exchange $exchange): void;
}