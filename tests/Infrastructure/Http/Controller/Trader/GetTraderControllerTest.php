<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller\Trader;

use ApiTestCase\JsonApiTestCase;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Symbol;
use StockExchange\Tests\Helpers\EventStoreSeeder;

class GetTraderControllerTest extends JsonApiTestCase
{
    use PHPMatcherAssertions;

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

        $expectedShares = [];
        foreach ($trader->shares() as $share) {
            $expectedShares[$share->id()->toString()] = [
                "id" => "@uuid@",
                "symbol" => "@string@",
                "owner_id" => "@uuid@"
            ];
        }

        $expected = json_encode([
            "id" => "@uuid@",
            "shares" => $expectedShares
        ]);

        $this->assertMatchesPattern($expected, $this->client->getResponse()->getContent());
    }
}
