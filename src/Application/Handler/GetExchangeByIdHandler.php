<?php

namespace StockExchange\Application\Handler;

use ArrayIterator;
use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\Exchange;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetExchangeByIdHandler implements MessageHandlerInterface
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(GetExchangeByIdQuery $query): Exchange
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

        return Exchange::restoreStateFromEvents($getExchangeQuery->getState());
    }
}
