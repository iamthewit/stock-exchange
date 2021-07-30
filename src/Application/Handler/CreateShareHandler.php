<?php

namespace StockExchange\Application\Handler;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\StockExchange\Share;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateShareHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateShareCommand $command): void
    {
        $command->exchange()->createShare($command->shareId(), $command->symbol());

        foreach ($command->exchange()->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $command->exchange()->clearDispatchableEvents();
    }
}

