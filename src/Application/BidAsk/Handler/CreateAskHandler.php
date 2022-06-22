<?php

namespace StockExchange\Application\BidAsk\Handler;

use StockExchange\Application\BidAsk\Command\CreateAskCommand;
use StockExchange\StockExchange\BidAsk\Ask;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateAskHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateAskCommand $command)
    {
        $ask = Ask::create(
            $command->id(),
            $command->exchangeId(),
            $command->traderId(),
            $command->symbol(),
            $command->price()
        );

        // dispatch aggregate events
        foreach ($ask->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $ask->clearDispatchableEvents();

        // TODO: write changes to AskWriteRepository

        return $ask; // TODO: remove this
    }
}
