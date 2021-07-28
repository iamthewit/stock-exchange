<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateTraderCommand;
use StockExchange\StockExchange\Trader;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateTraderHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateTraderCommand $command): void
    {
        $trader = Trader::create($command->id());

        foreach ($trader->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }
    }
}
