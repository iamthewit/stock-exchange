<?php


namespace StockExchange\Application\BidAsk\Handler;

use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class RemoveBidHandler
 * @package StockExchange\Application\BidAsk\Handler
 */
class RemoveBidHandler implements MessageHandlerInterface
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

    public function __invoke(RemoveBidCommand $command)
    {
        $bid = $this->exchangeReadRepository->findBidById($command->id()->toString());

//        dd($bid);

        $bid->remove();

        foreach ($bid->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        $bid->clearDispatchableEvents();

        return $bid;
    }
}