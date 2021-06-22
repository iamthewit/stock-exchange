<?php

namespace StockExchange\Application\Handler;

use ArrayIterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\Exchange;

class GetExchangeByIdHandler
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(GetExchangeByIdQuery $query)
    {
        // rebuild the state of the exchange
        $getExchangeQuery = $this->projectionManager->createQuery();
        $getExchangeQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Exchange::class . '-' . $query->id())
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        return Exchange::restoreStateFromEvents(
            new ArrayIterator($getExchangeQuery->getState())
        );
    }
}
