<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Command\AllocateShareToTraderCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AllocateShareToTraderHandler
 * @package StockExchange\Application\Handler
 */
class AllocateShareToTraderHandler implements MessageHandlerInterface
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

    public function __invoke(AllocateShareToTraderCommand $command): void
    {
        $exchange = $this->exchangeReadRepository->findById($command->exchangeId()->toString());
        $share = $exchange->shares()->findById($command->shareId());
        $trader = $exchange->traders()->findById($command->traderId());

        $exchange->allocateShareToTrader($share, $trader);

        // dispatch aggregate events
        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        $this->exchangeWriteRepository->store($exchange);
    }
}
