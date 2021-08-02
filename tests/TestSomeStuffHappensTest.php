<?php

namespace StockExchange\Tests;

use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Handler\GetAllTradersHandler;
use StockExchange\Application\Query\GetAllTradersQuery;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TestSomeStuffHappensTest extends KernelTestCase
{
    public function testItDoesSomeThings()
    {
        $this->markTestIncomplete();
        self::bootKernel();

        $projectionManager = static::$container->get(ProjectionManager::class);

        $query = new GetAllTradersQuery();
        $handler = new GetAllTradersHandler($projectionManager);

        $traderCollection = $handler($query);

//        \Kint::dump(json_encode($traderCollection));
    }
}
