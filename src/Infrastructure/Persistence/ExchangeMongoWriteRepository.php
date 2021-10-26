<?php

namespace StockExchange\Infrastructure\Persistence;

use DateTime;
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
            ['$set' => $this->createExchangeArray($exchange)],
            ['upsert' => true]
        );
    }

    /**
     * @param Exchange $exchange
     *
     * @return array
     */
    protected function createExchangeArray(Exchange $exchange): mixed
    {
        return array_merge(
            ['_id' => $exchange->id()->toString()],
            json_decode(json_encode($exchange), true),
            [
                'last_applied_event' => array_merge(
                    $exchange->lastAppliedEvent()->toArray(),
                    [
                        'created_at' => $exchange
                            ->lastAppliedEvent()
                            ->createdAt()
                            ->format(DateTime::ISO8601)
                    ]
                )
            ]
        );
    }
}