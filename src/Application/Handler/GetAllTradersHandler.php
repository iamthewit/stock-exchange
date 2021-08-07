<?php

namespace StockExchange\Application\Handler;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetAllTradersQuery;
use StockExchange\StockExchange\Event\TraderCreated;
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
            // Use this if we want the trader with all of there event history associated
//            ->whenAny(function (array $state, Message $event): array {
//                $state[$event->metadata()['_aggregate_id']][] = $event;
//
//                return $state;
//            })
            // Use this is we just want a basic list of traders:
            ->when([
                TraderCreated::class => function (array $state, Message $event): array {
                    $state[$event->metadata()['_aggregate_id']][] = $event;
                    return $state;
                },
                // if we had a TraderDeleted event we would wont to account for that here
                // so that we don't return traders that no longer exist.
                // OR maybe we should habe a more specific command/handler for returning all
                // active traders and leave this one to return all traders no matter what...?
            ])
            ->run()
        ;

        $traders = [];
        foreach ($getTradersQuery->getState() as $traderEvents) {
            $traders[] = Trader::restoreStateFromEvents($traderEvents);
        }

        return new TraderCollection($traders);
    }
}
