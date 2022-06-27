<?php


namespace StockExchange\Application\Exchange\Handler;

use StockExchange\Application\Exchange\Command\RemoveBidFromExchangeCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class RemoveAskFromExchangeHandler
 * @package StockExchange\Application\Exchange\Handler
 */
class RemoveBidFromExchangeHandler implements MessageHandlerInterface
{
    private MessageBusInterface $messageBus;
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
    ) {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(RemoveBidFromExchangeCommand $command)
    {
        $exchange = $this->exchangeReadRepository->findExchangeById($command->exchangeId()->toString());

        $exchange->removeBid($command->id());

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}