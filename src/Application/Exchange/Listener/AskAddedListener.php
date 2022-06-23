<?php


namespace StockExchange\Application\Exchange\Listener;


use Ramsey\Uuid\Uuid;
use StockExchange\Application\Exchange\Command\AddAskToExchangeCommand;
use StockExchange\StockExchange\BidAsk\Event\AskAdded;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class AskAddedListener implements MessageHandlerInterface
{
    use HandleTrait;

    /**
     * AskAddedListener constructor.
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(AskAdded $event)
    {
//        $this->handle(
//            new AddAskToExchangeCommand(
//                Uuid::fromString($event->payload()['exchangeId']),
//                Uuid::fromString($event->payload()['id']),
//                Uuid::fromString($event->payload()['traderId']),
//                Symbol::fromValue($event->payload()['symbol']['value']),
//                Price::fromValue($event->payload()['price']['value'])
//            )
//        );
    }
}