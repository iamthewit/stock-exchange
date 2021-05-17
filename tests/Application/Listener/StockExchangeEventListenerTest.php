<?php

namespace StockExchange\Tests\Application\Listener;

use Prooph\EventStore\Projection\ProjectionManager;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateBidCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Handler\CreateBidHandler;
use StockExchange\Application\Handler\CreateExchangeHandler;
use StockExchange\Application\Handler\GetExchangeByIdHandler;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\Event\BidAddedToExchange;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

class StockExchangeEventListenerTest extends KernelTestCase
{
    public function testItIsListening()
    {
        // This test is only for the purpose
        // of checking that the event store
        // is working with a real database
        // during development.

        // If you want to see data appear in the
        // event store setup your database, set
        // your creds in the .env.test file
        // and comment out this line:
        $this->markTestSkipped();

        self::bootKernel();

        $messageBus = self::$container->get(MessageBusInterface::class);
        $projectionManager = self::$container->get(ProjectionManager::class);

        $exchangeId = Uuid::uuid4();

        // create the exchange
        $command = new CreateExchangeCommand($exchangeId);
        $handler = new CreateExchangeHandler($messageBus);
        $handler($command);

        // get the exchange
        $query = new GetExchangeByIdQuery($exchangeId);
        $handler = new GetExchangeByIdHandler($projectionManager);
        $exchange = $handler($query);

        // add a bid
        $command = new CreateBidCommand(
            $exchange,
            Uuid::uuid4(),
            Trader::create(Uuid::uuid4()),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );
        $handler = new CreateBidHandler($messageBus);
        $handler($command);

        // add an ask

        // TODO: mock the event store so that proper tests can be written
    }
}
