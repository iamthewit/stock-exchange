<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller;

use ApiTestCase\JsonApiTestCase;
use Coduo\PHPMatcher\Matcher\JsonMatcher;
use Coduo\PHPMatcher\PHPMatcher;
use Kint\Kint;
use StockExchange\Infrastructure\Http\Controller\TradeController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TradeControllerTest extends JsonApiTestCase
{
    public function testItDoesSomething()
    {
//        $this->markTestIncomplete();
        
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
//        $client = static::createClient();

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

//        $this->assertTrue(
//            $match
//        );

        d($matcher->error());

    }
}
