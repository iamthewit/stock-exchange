<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateBidCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateBidHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateBidCommand $command): void
    {
        $exchange = $command->exchange();
        $exchange->bid(
            $command->id(),
            $command->trader(),
            $command->symbol(),
            $command->price()
        );

        // dispatch aggregate events
        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
