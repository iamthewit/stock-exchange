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
    public function testItDoesSomething()
    {
//        $this->markTestIncomplete();
        
        // Request a specific page
        $this->client->request('GET', '/trader');

        // Validate a successful response and some content
//        $this->assertResponseIsSuccessful();
//        $this->assertJson($client->getResponse()->getContent());

//        $this->assertResponse(
//            $this->client->getResponse(),
//            'trader/index'
//        );

        $matcher = new PHPMatcher();

        $match = $matcher->match(
            $this->client->getResponse()->getContent(),
            '
                [
                  {
                    "id": @uuid@,
                    "shares": @array@
                  },
                  @...@
                ]
                '
        );

        d($this->client->getResponse()->getContent(), $matcher->error());

        $this->assertTrue($match);

    }
}
