<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\StockExchange\AskCollection;
use StockExchange\StockExchange\BidCollection;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ShareCollection;
use StockExchange\StockExchange\SymbolCollection;
use StockExchange\StockExchange\TradeCollection;
use StockExchange\StockExchange\TraderCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke(CreateExchangeCommand $command): void
    {
        $exchange = Exchange::create(
            $command->id(),
            new SymbolCollection([]),
            new BidCollection([]),
            new AskCollection([]),
            new TradeCollection([]),
            new TraderCollection([]),
            new ShareCollection([]),
        );

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();
    }
}
