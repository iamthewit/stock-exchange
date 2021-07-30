<?php

namespace StockExchange\Infrastructure\CLI;

use Kint\Kint;
use Ramsey\Uuid\Uuid;
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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SeedEventStreamDatabaseCommand extends Command
{
    protected static $defaultName = 'seed:event-stream-db';
    protected static $defaultDescription = 'Add a short description for your command';

    private $params;
    private MessageBusInterface $messageBus;
    private QueryHandlerBus $queryHandlerBus;

    /**
     * SeedEventStreamDatabaseCommand constructor.
     *
     * @param ParameterBagInterface $params
     * @param MessageBusInterface $messageBus
     * @param QueryHandlerBus $queryHandlerBus
     */
    public function __construct(
        ParameterBagInterface $params,
        MessageBusInterface $messageBus,
        QueryHandlerBus $queryHandlerBus
    )
    {
        $this->params = $params;
        $this->messageBus = $messageBus;
        $this->queryHandlerBus = $queryHandlerBus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // create the exchange
        $exchangeId = $this->params->get('stock_exchange.default_exchange_id');
        $exchangeId = Uuid::fromString($exchangeId);
        $this->messageBus->dispatch(new CreateExchangeCommand($exchangeId));

        // get the exchange by id
        /** @var Exchange $exchange */
        $exchange = $this->queryHandlerBus->query(new GetExchangeByIdQuery($exchangeId));

        $traderOne = $this->createTraderWithShares($exchange, Symbol::fromValue('FOO'));

        $traderTwo = $this->createTraderWithShares($exchange, Symbol::fromValue('BAR'));

//        Kint::dump($traderOne, $traderTwo);

        $io->success('Re-seed complete!.');

        return Command::SUCCESS;
    }

    /**
     * @param Exchange $exchange
     * @param Symbol   $symbol
     *
     * @return Trader
     */
    protected function createTraderWithShares(Exchange $exchange, Symbol $symbol): Trader
    {
        // create a trader
        $traderId = Uuid::uuid4();
        $this->messageBus->dispatch(new CreateTraderCommand($exchange, $traderId));

        // create some shares
        for ($i = 0; $i < 10; $i++) {
            // get trader by id
            /** @var Trader $trader */
            $trader = $this->queryHandlerBus->query(new GetTraderByIdQuery($traderId));

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

        return $this->queryHandlerBus->query(new GetTraderByIdQuery($traderId));
    }
}
