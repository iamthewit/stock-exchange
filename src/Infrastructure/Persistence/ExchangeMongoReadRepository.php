<?php

namespace StockExchange\Infrastructure\Persistence;

use MongoDB\Client;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;

class ExchangeMongoReadRepository implements ExchangeReadRepositoryInterface
{
    private Client $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function findById(string $id): Exchange
    {
        $collection = $this->client->stock_exchange->exchanges;

        $result = $collection->findOne(
            ['_id' => $id],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );

        return Exchange::restoreFromValues($result);
    }
}