<?php

namespace StockExchange\Infrastructure\Persistence;

use MongoDB\Client;

class MongoClientFactory
{
    /**
     * @param string $uri
     *
     * @return Client
     */
    public static function createClient(string $uri): Client
    {
        return new Client($uri);
    }
}