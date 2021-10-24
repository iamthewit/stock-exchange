<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\CreateBidCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CreateBidHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(CreateBidCommand $command): void
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId());
        $trader = $exchange->traders()->findById($command->traderId());

        $exchange->bid(
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
    }
}
