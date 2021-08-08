<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller;

use ApiTestCase\JsonApiTestCase;
use Coduo\PHPMatcher\Matcher\JsonMatcher;
use Coduo\PHPMatcher\PHPMatcher;
use Kint\Kint;
use StockExchange\Infrastructure\Http\Controller\TradeController;
use PHPUnit\Framework\TestCase;
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
}
