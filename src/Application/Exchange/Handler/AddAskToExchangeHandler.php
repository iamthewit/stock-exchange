<?php


namespace StockExchange\Application\Exchange\Handler;

use StockExchange\Application\Exchange\Command\AddAskToExchangeCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class AddAskToExchangeHandler
 * @package StockExchange\Application\Exchange\Handler
 */
class AddAskToExchangeHandler implements MessageHandlerInterface
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

    public function __invoke(AddAskToExchangeCommand $command)
    {
        $exchange = $this->exchangeReadRepository->findExchangeById($command->exchangeId()->toString());

        $exchange->ask(
            $command->id(),
            $command->traderId(),
            $command->symbol(),
            $command->price()
        );

        foreach ($exchange->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $exchange->clearDispatchableEvents();

        return $exchange; // TODO: remove this
    }
}