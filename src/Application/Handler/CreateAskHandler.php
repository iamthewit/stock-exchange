<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateAskCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateAskHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateAskCommand $command): void
    {
        $exchange = $command->exchange();
        $exchange->ask(
            $command->id(),
            $command->trader(),
            $command->symbol(),
            $command->price()
        );

//        d($exchange->dispatchableEvents());die;

        // dispatch aggregate events
        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();
    }
}
