<?php

namespace StockExchange\Tests;

use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Handler\GetAllTradersHandler;
use StockExchange\Application\Query\GetAllTradersQuery;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use PHPUnit\Framework\TestCase;

class TestSomeStuffHappensTest extends KernelTestCase
{
    public function testItDoesSomeThings()
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container

        $projectionManager = static::$container->get(ProjectionManager::class);

        $query = new GetAllTradersQuery();
        $handler = new GetAllTradersHandler($projectionManager);

        $handler($query);
    }
}
