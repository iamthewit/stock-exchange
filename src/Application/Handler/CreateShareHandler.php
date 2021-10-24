<?php

namespace StockExchange\Application\Handler;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;
use StockExchange\StockExchange\Share;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateShareHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;
    private ExchangeWriteRepositoryInterface $exchangeWriteRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
        ExchangeWriteRepositoryInterface $exchangeWriteRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
        $this->exchangeWriteRepository = $exchangeWriteRepository;
    }

    public function __invoke(CreateShareCommand $command): void
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId());

        $exchange->createShare($command->shareId(), $command->symbol());

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        $this->exchangeWriteRepository->store($exchange);
    }
}

