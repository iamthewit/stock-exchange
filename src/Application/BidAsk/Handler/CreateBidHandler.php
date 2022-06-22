<?php

namespace StockExchange\Application\BidAsk\Handler;

use StockExchange\Application\BidAsk\Command\CreateBidCommand;
use StockExchange\StockExchange\BidAsk\Bid;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateBidHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateBidCommand $command)
    {
        $bid = Bid::create(
            $command->id(),
            $command->exchangeId(),
            $command->traderId(),
            $command->symbol(),
            $command->price()
        );

        // dispatch aggregate events
        foreach ($bid->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $bid->clearDispatchableEvents();

        // TODO: store with BidWriteRepository

        return $bid; // TODO: remove this
    }
}
