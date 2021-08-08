<?php

namespace StockExchange\Tests\Helpers;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\Application\Command\AllocateShareToTraderCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\Application\Command\CreateTraderCommand;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\Application\Query\GetShareByIdQuery;
use StockExchange\Application\Query\GetTraderByIdQuery;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class CreateExchange
 * @package StockExchange\Tests\Helpers
 */
class EventStoreSeeder
{
    private MessageBusInterface $messageBus;
    private QueryHandlerBus $queryHandlerBus;

    /**
     * EventStoreSeeder constructor.
     *
     * @param MessageBusInterface $messageBus
     * @param QueryHandlerBus     $queryHandlerBus
     */
    public function __construct(MessageBusInterface $messageBus, QueryHandlerBus $queryHandlerBus)
    {
        $this->messageBus = $messageBus;
        $this->queryHandlerBus = $queryHandlerBus;
    }

    public function createExchange(UuidInterface $id): Exchange
    {
        $this->messageBus->dispatch(new CreateExchangeCommand($id));

        return $this->queryHandlerBus->query(new GetExchangeByIdQuery($id));
    }

    public function createTraderWithShares(UuidInterface $id, Exchange $exchange, Symbol $symbol): Trader
    {
        // create a trader
        $this->messageBus->dispatch(new CreateTraderCommand($exchange, $id));

        // create some shares
        for ($i = 0; $i < 10; $i++) {
            // get trader by id
            /** @var Trader $trader */
            $trader = $this->queryHandlerBus->query(new GetTraderByIdQuery($id));

            $shareId = Uuid::uuid4();
            $this->messageBus->dispatch(
                new CreateShareCommand(
                    $exchange,
                    $shareId,
                    $symbol
                )
            );

            /** @var Share $share */
            $share = $this->queryHandlerBus->query(new GetShareByIdQuery($shareId));

            // allocate share to trader
            $this->messageBus->dispatch(new AllocateShareToTraderCommand($exchange, $share, $trader));
        }

        return $this->queryHandlerBus->query(new GetTraderByIdQuery($id));
    }
}
