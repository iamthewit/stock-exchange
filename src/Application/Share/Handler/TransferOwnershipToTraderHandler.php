<?php

namespace StockExchange\Application\Share\Handler;

use StockExchange\Application\Share\Command\TransferOwnershipToTraderCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\ExchangeWriteRepositoryInterface;
use StockExchange\StockExchange\Share\Share;
use StockExchange\StockExchange\Symbol;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AllocateShareToTraderHandler
 * @package StockExchange\Application\Handler
 */
class TransferOwnershipToTraderHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
        ExchangeWriteRepositoryInterface $exchangeWriteRepository
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(TransferOwnershipToTraderCommand $command)
    {
        $share = $this->exchangeReadRepository->findShareById($command->shareId()->toString());

        $share->transferOwnershipToTrader($command->traderId());

        // dispatch aggregate events
        foreach ($share->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $share->clearDispatchableEvents();

        // TODO: store changes in share repo

        return $share; // TODO: remove this
    }
}
