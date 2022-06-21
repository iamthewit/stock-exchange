<?php


namespace StockExchange\Application\Share\Listener;


use Ramsey\Uuid\Uuid;
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
        // load one shares with:
        // - symbol $event->payload()['ask']['symbol']
        // - traderId $event->payload()['ask']['traderId']

        // transfer the ownership of that share
        $shareIds = $this->eventStoreReadRepository->findShareIdsBySymbolAndTraderId(
            Symbol::fromValue($event->payload()['ask']['symbol']['value']),
            Uuid::fromString($event->payload()['ask']['traderId'])
        );

        $share = $this->eventStoreReadRepository->findShareById($shareIds[array_rand($shareIds)]);

        // Transfer share from ASKer to BIDer
        $this->handle(
            new TransferOwnershipToTraderCommand(
                Uuid::fromString($event->metadata()['_aggregate_id']),
                $share->id(),
                Uuid::fromString($event->payload()['bid']['traderId'])
            )
        );
    }
}