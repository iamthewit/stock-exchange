<?php

namespace StockExchange\Infrastructure\CLI;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\Application\Command\AllocateShareToTraderCommand;
use StockExchange\Application\Command\CreateAskCommand;
use StockExchange\Application\Command\CreateBidCommand;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\Application\Command\CreateTraderCommand;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetTraderByIdQuery;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SeedEventStreamDatabaseCommand extends Command
{
    protected static $defaultName = 'seed:event-stream-db';
    protected static $defaultDescription = 'Add a short description for your command';

    private ParameterBagInterface $params;
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
    ) {
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
        $exchangeId = Uuid::fromString(
            $this->params->get('stock_exchange.default_exchange_id')
        );
        $this->messageBus->dispatch(new CreateExchangeCommand($exchangeId));
        $io->info('Created Exchange: ' . $exchangeId->toString());

        // create a trader and some shares
        $traderOne = $this->createTraderWithShares($exchangeId, Symbol::fromValue('FOO'));
        $io->info('Created Trader: ' . $traderOne->id()->toString());

        // create a bid for trader one
        // $traderOne is BIDDING 100 for BAR (buyer)
        $bidOneId = Uuid::uuid4();
        $this->messageBus->dispatch(
            new CreateBidCommand(
                $exchangeId,
                $bidOneId,
                $traderOne->id(),
                Symbol::fromValue('BAR'),
                Price::fromValue(100)
            )
        );
        $io->info('Created Bid: ' . $bidOneId->toString());

        // create another trader and some shares
        $traderTwo = $this->createTraderWithShares($exchangeId, Symbol::fromValue('BAR'));
        $io->info('Created Trader: ' . $traderTwo->id()->toString());

        // create a bid for trader two
        // $traderTwo is BIDDING 100 for FOO (buyer)
        $bidTwoId = Uuid::uuid4();
        $this->messageBus->dispatch(
            new CreateBidCommand(
                $exchangeId,
                $bidTwoId,
                $traderTwo->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $io->info('Created Bid: ' . $bidTwoId->toString());

        // $traderOne is ASKING 100 for FOO (seller)
        $askOneId = Uuid::uuid4();
        $this->messageBus->dispatch(
            new CreateAskCommand(
                $exchangeId,
                $askOneId,
                $traderOne->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(100)
            )
        );
        $io->info('Created Ask: ' . $askOneId->toString());

        $io->success('Re-seed complete!.');

        return Command::SUCCESS;
    }

    /**
     * @param UuidInterface $exchangeId
     * @param Symbol $symbol
     *
     * @return Trader
     */
    protected function createTraderWithShares(UuidInterface $exchangeId, Symbol $symbol): Trader
    {
        // create a trader
        $traderId = Uuid::uuid4();
        $this->messageBus->dispatch(new CreateTraderCommand($exchangeId, $traderId));

        // create some shares
        for ($i = 0; $i < 3; $i++) {
            $shareId = Uuid::uuid4();

            $this->messageBus->dispatch(
                new CreateShareCommand(
                    $exchangeId,
                    $shareId,
                    $symbol
                )
            );

            // allocate share to trader
            $this->messageBus->dispatch(new AllocateShareToTraderCommand($exchangeId, $shareId, $traderId));
        }

        return $this->queryHandlerBus->query(new GetTraderByIdQuery($traderId, $exchangeId));
    }
}
