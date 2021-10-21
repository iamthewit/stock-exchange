<?php

namespace StockExchange\Application\Handler;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetAllTradersQuery;
use StockExchange\StockExchange\Event\Trader\TraderCreated;
use StockExchange\StockExchange\Exchange;
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
        // rebuild the state of the exchange
        $getExchangeQuery = $this->projectionManager->createQuery();
        $getExchangeQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Exchange::class . '-' . $query->exchangeId())
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        $exchange = Exchange::restoreStateFromEvents($getExchangeQuery->getState());

        return $exchange->traders();
    }
}
