<?php

namespace StockExchange\StockExchange;

use StockExchange\StockExchange\Exception\ExchangeNotFoundException;

interface ExchangeReadRepositoryInterface
{
    /**
     * @param string $id
     *
     * @return Exchange
     *
     * @throws ExchangeNotFoundException
     */
    public function findById(string $id): Exchange;
}
