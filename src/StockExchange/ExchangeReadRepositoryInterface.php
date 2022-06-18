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

    public function findExchangeById(string $id): Exchange\Exchange;

    public function findShareById(string $id): \StockExchange\StockExchange\Share\Share;

    public function findAskById(string $id): \StockExchange\StockExchange\BidAsk\Ask;

    public function findBidById(string $id): \StockExchange\StockExchange\BidAsk\Bid;
}
