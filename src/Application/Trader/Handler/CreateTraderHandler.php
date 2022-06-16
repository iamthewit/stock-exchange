<?php

namespace StockExchange\Application\Trader\Handler;

use StockExchange\Application\Trader\Command\CreateTraderCommand;
use StockExchange\StockExchange\Trader\Trader;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateTraderHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateTraderCommand $command)
    {
        $trader = Trader::create($command->traderId());

        foreach ($trader->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $trader->clearDispatchableEvents();

        // TODO: store the trader via the TraderWriteRepo

        return $trader; // TODO: remove this
    }
}
