<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller\Trader;

use ApiTestCase\JsonApiTestCase;
use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Symbol;
use StockExchange\Tests\Helpers\EventStoreSeeder;

class GetTraderControllerTest extends JsonApiTestCase
{
    public function testTheResourceRouteReturnsAJSONTraderObject()
    {
        /** @var EventStoreSeeder $eventStoreSeeder */
        $eventStoreSeeder = $this::$container->get(EventStoreSeeder::class);

        $eventStoreSeeder->dropDatabase();
        $eventStoreSeeder->createDatabase();

        $exchange = $eventStoreSeeder->createExchange(
            Uuid::fromString($this::$container->getParameter('stock_exchange.default_exchange_id'))
        );

        $traderId = Uuid::uuid4();
        $trader = $eventStoreSeeder->createTraderWithShares(
            $traderId,
            $exchange,
            Symbol::fromValue('FOO'),
            2
        );

        $this->client->request('GET', '/trader/' . $trader->id()->toString());

        $this->assertResponse(
            $this->client->getResponse(),
            'trader/resource'
        );
    }
}
