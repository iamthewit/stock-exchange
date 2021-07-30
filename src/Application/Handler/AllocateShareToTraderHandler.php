<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\AllocateShareToTraderCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AllocateShareToTraderHandler
 * @package StockExchange\Application\Handler
 */
class AllocateShareToTraderHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(AllocateShareToTraderCommand $command): void
    {
        $exchange = $command->exchange();
        $exchange->allocateShareToTrader(
            $command->share(),
            $command->trader()
        );

        // dispatch aggregate events
        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();
    }
}
