<?php


namespace StockExchange\Application\BidAsk\Listener;


use Ramsey\Uuid\Uuid;
use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\Application\Share\Command\TransferOwnershipToTraderCommand;
use StockExchange\Infrastructure\Persistence\ExchangeMySqlEventStoreReadRepository;
use StockExchange\StockExchange\Exchange\Event\TradeExecuted;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Symbol;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TradeExecutedListener implements MessageHandlerInterface
{
    use HandleTrait;

    private ExchangeReadRepositoryInterface $eventStoreReadRepository;

    /**
     * TradeExecutedListener constructor.
     *
     * @param ExchangeReadRepositoryInterface $eventStoreReadRepository
     */
    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $eventStoreReadRepository
    )
    {
        $this->messageBus = $messageBus;
        $this->eventStoreReadRepository = $eventStoreReadRepository;
    }


    public function __invoke(TradeExecuted $event)
    {
        // once a trade is executed remove the bid and the ask

//        $bid = $this->eventStoreReadRepository->findBidById($event->payload()['bid']['bidId']);

//        $ask = $this->eventStoreReadRepository->findAskById($event->payload()['ask']['askId']);

        // remove the bid
//        $this->handle(
//            new RemoveBidCommand(
//                Uuid::fromString($event->metadata()['_aggregate_id']), // the trade is part of the exchange aggregate - so the aggregate id here is the exchange id
//                Uuid::fromString($event->payload()['bid']['bidId'])
//            )
//        );

        // remove the ask
//        $this->handle(
//            new RemoveAskCommand(
//                Uuid::fromString($event->metadata()['_aggregate_id']), // the trade is part of the exchange aggregate - so the aggregate id here is the exchange id
//                Uuid::fromString($event->payload()['ask']['askId'])
//            )
//        );
    }
}