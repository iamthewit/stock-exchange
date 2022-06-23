<?php


namespace StockExchange\Application\BidAsk\Handler;

use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class RemoveAskHandler
 * @package StockExchange\Application\BidAsk\Handler
 */
class RemoveAskHandler implements MessageHandlerInterface
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

    public function __invoke(RemoveAskCommand $command)
    {
        $ask = $this->exchangeReadRepository->findAskById($command->id()->toString());

        dd($ask);

        $ask->remove();

        foreach ($ask->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $ask->clearDispatchableEvents();

        return $ask;
    }
}