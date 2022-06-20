<?php


namespace StockExchange\Application\BidAsk\Listener;


use Ramsey\Uuid\Uuid;
use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\StockExchange\Exchange\Event\BidRemovedFromExchange;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class BidRemovedFromExchangeListener implements MessageHandlerInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(BidRemovedFromExchange $event)
    {
        $this->handle(
            new RemoveBidCommand(
                Uuid::fromString($event->metadata()['_aggregate_id']),
                Uuid::fromString($event->payload()['bidId'])
            )
        );
    }
}