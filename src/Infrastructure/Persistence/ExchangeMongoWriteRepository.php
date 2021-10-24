<?php

namespace StockExchange\Infrastructure\Persistence;

use MongoDB\Client;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;

class ExchangeMongoWriteRepository implements ExchangeWriteRepositoryInterface
{
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function store(Exchange $exchange): void
    {
        $collection = $this->client->stock_exchange->exchanges;

        $collection->updateOne(
            ['_id' => $exchange->id()->toString()],
            ['$set' => ['_id' => $exchange->id()->toString()] + json_decode(json_encode($exchange), true)],
            ['upsert' => true]
        );
    }
}