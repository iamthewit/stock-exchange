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
//            ->fromStream('$ct-' . Trader::class)
            ->fromCategory(Trader::class)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        \Kint::dump($getTradersQuery->getState());

//        return Trader::restoreStateFromEvents($getTradersQuery->getState());
    }
}