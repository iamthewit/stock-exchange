<?php

namespace StockExchange\Application\Handler;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use StockExchange\Application\Query\GetShareByIdQuery;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Share;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetShareByIdHandler implements MessageHandlerInterface
{
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(GetShareByIdQuery $query): Share
    {
        // rebuild the state of the trader
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

        $exchange =  Exchange::restoreStateFromEvents($getExchangeQuery->getState());

        return $exchange->shares()->findById($query->id());
    }
}
