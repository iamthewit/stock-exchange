<?php

namespace StockExchange\Tests\Application\Listener;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Handler\CreateExchangeHandler;
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

        $command = new CreateExchangeCommand(Uuid::uuid4());

        $handler = new CreateExchangeHandler($messageBus);

        $handler($command);

        // TODO: mock the event store so that proper tests can be written
    }
}
