<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller;

use ApiTestCase\JsonApiTestCase;
use Coduo\PHPMatcher\Matcher\JsonMatcher;
use Coduo\PHPMatcher\PHPMatcher;
use Kint\Kint;
use Ramsey\Uuid\Uuid;
use StockExchange\Infrastructure\Http\Controller\TradeController;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;
use StockExchange\Tests\Helpers\EventStoreSeeder;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListTradersControllerTest extends JsonApiTestCase
{
    public function testTheIndexRouteReturnsAJSONArrayOfTraderUUIDs()
    {
        /** @var EventStoreSeeder $eventStoreSeeder */
        $eventStoreSeeder = $this::$container->get(EventStoreSeeder::class);

        $eventStoreSeeder->dropDatabase();
        $eventStoreSeeder->createDatabase();

        $exchange = $eventStoreSeeder->createExchange(
            Uuid::fromString($this::$container->getParameter('stock_exchange.default_exchange_id'))
        );

        $eventStoreSeeder->createTraderWithShares(Uuid::uuid4(), $exchange, Symbol::fromValue('FOO'), 1);

        $exchange = $eventStoreSeeder->getExchangeById($exchange->id());
        $eventStoreSeeder->createTraderWithShares(Uuid::uuid4(), $exchange, Symbol::fromValue('FOO'), 1);

        $this->client->request('GET', '/trader');

        $this->assertResponse(
            $this->client->getResponse(),
            'trader/index' // tests/Infrastructure/Http/Responses/trader/index.json
        );

        // The assertResponse method does a few things,
        // one of which is calling PHPMatcher with something
        // like this:
//        $matcher = new PHPMatcher();
//        $match = $matcher->match(
//            $this->client->getResponse()->getContent(),
//            '
//            [
//              {
//                "id": @uuid@
//              },
//              @...@
//            ]
//            '
//        );

        // useful for debugging:
//        d($this->client->getResponse()->getContent(), $matcher->error());

//        $this->assertTrue($match);
    }
}
