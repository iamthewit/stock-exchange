<?php

namespace StockExchange\Application\Handler;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetTraderByIdQuery;
use StockExchange\StockExchange\Trader;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetTraderByIdHandler implements MessageHandlerInterface
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(GetTraderByIdQuery $query): Trader
    {
        // rebuild the state of the trader
        $getTraderQuery = $this->projectionManager->createQuery();
        $getTraderQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Trader::class . '-' . $query->id())
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        return Trader::restoreStateFromEvents($getTraderQuery->getState());
    }
}
