<?php

namespace StockExchange\Tests\Helpers;

use PDO;
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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class CreateExchange
 * @package StockExchange\Tests\Helpers
 */
class EventStoreSeeder
{
    private ParameterBagInterface $params;
    private MessageBusInterface $messageBus;
    private QueryHandlerBus $queryHandlerBus;

    /**
     * EventStoreSeeder constructor.
     *
     * @param ParameterBagInterface $params
     * @param MessageBusInterface   $messageBus
     * @param QueryHandlerBus       $queryHandlerBus
     */
    public function __construct(
        ParameterBagInterface $params,
        MessageBusInterface $messageBus,
        QueryHandlerBus $queryHandlerBus
    ) {
        $this->params = $params;
        $this->messageBus = $messageBus;
        $this->queryHandlerBus = $queryHandlerBus;
    }

    public function dropDatabase()
    {
        $pdo = new PDO($this->params->get('stock_exchange.mysql_dsn_no_db_specified'));

        $statement = $pdo->prepare('DROP DATABASE ' . $this->params->get('stock_exchange.db_name'));
        $statement->execute();
    }

    public function createDatabase()
    {
        $pdo = new PDO($this->params->get('stock_exchange.mysql_dsn_no_db_specified'));

        $statement = $pdo->prepare('CREATE DATABASE ' . $this->params->get('stock_exchange.db_name'));
        $statement->execute();

        // seed the base tables

        $pdo = new PDO($this->params->get('stock_exchange.mysql_dsn'));

        $statement = $pdo->prepare(
            file_get_contents(__DIR__ . './../../config/scripts/mysql/01_event_streams_table.sql')
        );
        $statement->execute();

        $statement = $pdo->prepare(
            file_get_contents(__DIR__ . './../../config/scripts/mysql/02_projections_table.sql')
        );
        $statement->execute();
    }

    public function createExchange(UuidInterface $id): Exchange
    {
        $this->messageBus->dispatch(new CreateExchangeCommand($id));

        return $this->getExchangeById($id);
    }

    public function createTraderWithShares(
        UuidInterface $id,
        Exchange $exchange,
        Symbol $symbol,
        int $shares = 10
    ): Trader {
        // create a trader
        $this->messageBus->dispatch(new CreateTraderCommand($exchange, $id));

        // create some shares
        for ($i = 0; $i < $shares; $i++) {
            // get trader by id
            /** @var Trader $trader */
            $trader = $this->queryHandlerBus->query(new GetTraderByIdQuery($id));
            $exchange = $this->getExchangeById($exchange->id());

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
            $exchange = $this->getExchangeById($exchange->id());
            $this->messageBus->dispatch(new AllocateShareToTraderCommand($exchange, $share, $trader));
        }

        return $this->queryHandlerBus->query(new GetTraderByIdQuery($id));
    }

    /**
     * @param UuidInterface $id
     * @return Exchange
     */
    public function getExchangeById(UuidInterface $id): Exchange
    {
        return $this->queryHandlerBus->query(new GetExchangeByIdQuery($id));
    }
}
