<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeWriteRepositoryInterface $exchangeWriteRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeWriteRepositoryInterface $exchangeWriteRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeWriteRepository = $exchangeWriteRepository;
    }

    public function __invoke(CreateExchangeCommand $command): void
    {
        $exchange = Exchange::create($command->id());

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        $this->exchangeWriteRepository->store($exchange);
    }
}
