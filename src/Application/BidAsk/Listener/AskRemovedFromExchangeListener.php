<?php


namespace StockExchange\Application\BidAsk\Listener;


use Ramsey\Uuid\Uuid;
use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\StockExchange\Exchange\Event\AskRemovedFromExchange;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class AskRemovedFromExchangeListener implements MessageHandlerInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(AskRemovedFromExchange $event)
    {
        $this->handle(
            new RemoveAskCommand(
                Uuid::fromString($event->metadata()['_aggregate_id']),
                Uuid::fromString($event->payload()['askId'])
            )
        );
    }
}