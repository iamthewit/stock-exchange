<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateAskCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateAskHandler implements MessageHandlerInterface
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

    public function __invoke(CreateAskCommand $command): void
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId()->toString());
        $trader = $exchange->traders()->findById($command->traderId());

        $exchange->ask(
            $command->id(),
            $trader,
            $command->symbol(),
            $command->price()
        );

        // dispatch aggregate events
        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        $this->exchangeWriteRepository->store($exchange);
    }
}
