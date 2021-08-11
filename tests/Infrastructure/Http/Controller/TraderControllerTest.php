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

class TraderControllerTest extends JsonApiTestCase
{
    public function testTheIndexRouteReturnsAJSONArrayOfTraderUUIDs()
    {
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
        $trader = $eventStoreSeeder->createTraderWithShares($traderId, $exchange, Symbol::fromValue('FOO'));

        $this->client->request('GET', '/trader/' . $trader->id()->toString());
        
        $this->assertResponse(
            $this->client->getResponse(),
            'trader/resource'
        );
    }
}
