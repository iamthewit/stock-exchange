<?php

namespace StockExchange\Infrastructure\Persistence;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;

class ExchangeMySqlEventStoreReadRepository implements ExchangeReadRepositoryInterface
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function findById(string $id): Exchange
    {
        // rebuild the state of the exchange
        $getExchangeQuery = $this->projectionManager->createQuery();
        $getExchangeQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Exchange::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Exchange::restoreStateFromEvents($getExchangeQuery->getState());
    }
}
