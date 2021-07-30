<?php

namespace StockExchange\Tests\Infrastructure\Http\Controller;

use StockExchange\Infrastructure\Http\Controller\TradeController;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TradeControllerTest extends WebTestCase
{
    public function testItDoesSomething()
    {
        $this->markTestIncomplete();
        
        // This calls KernelTestCase::bootKernel(), and creates a
        // "client" that is acting as the browser
        $client = static::createClient();

        // Request a specific page
        $crawler = $client->request('GET', '/trade');

        // Validate a successful response and some content
//        $this->assertResponseIsSuccessful();
//        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
