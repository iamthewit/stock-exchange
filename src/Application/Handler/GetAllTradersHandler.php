<?php

namespace StockExchange\Application\Handler;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetAllTradersQuery;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\TraderCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class GetAllTradersHandler
 * @package StockExchange\Application\Handler
 */
class GetAllTradersHandler implements MessageHandlerInterface
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(GetAllTradersQuery $query): TraderCollection
    {
        $getTradersQuery = $this->projectionManager->createQuery();
        $getTradersQuery
            ->init(function (): array {
                return [];
            })
            ->fromCategory(Trader::class)
            ->whenAny(function (array $state, Message $event): array {
                $state[$event->metadata()['_aggregate_id']][] = $event;

                return $state;
            })
            ->run()
        ;

        $traders = [];
        foreach ($getTradersQuery->getState() as $traderEvents) {
            $traders[] = Trader::restoreStateFromEvents($traderEvents);
        }

        return new TraderCollection($traders);
    }
}
