<?php

namespace StockExchange\Infrastructure\CLI;

use Kint\Kint;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\Application\Command\CreateTraderCommand;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetShareByIdQuery;
use StockExchange\Application\Query\GetTraderByIdQuery;
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
        $this->messageBus->dispatch(new CreateExchangeCommand(Uuid::fromString($exchangeId)));

        // create a trader

        $traderId = Uuid::uuid4();
        $this->messageBus->dispatch(new CreateTraderCommand($traderId));

        // get trader by id
        /** @var Trader $trader */
        $trader = $this->queryHandlerBus->query(new GetTraderByIdQuery($traderId));

//        Kint::dump($trader);die;

        // create some shares
        $shareId = Uuid::uuid4();
        $this->messageBus->dispatch(
            new CreateShareCommand(
                $shareId,
                Symbol::fromValue('FOO')
            )
        );

        // for each share
        /** @var Share $share */
        $share = $this->queryHandlerBus->query(new GetShareByIdQuery($shareId));

        // transfer ownership to trader

        // create command + handler to do this:
        $share->transferOwnershipToTrader($trader);

        // dispatch events
        // TODO: this should be done via the exchange as that is our aggregate root.
        // We need to add a new method to the exchange specifically for assigning
        // shares to a trader that have not yet been traded

        // This goes for everything. Currently we are creating traders and shares
        // directly via command handlers that dispatch events from the Trader and
        // Share objects. Instead we should be doing everything via the Exchange e.g:
            // Exchange::createTrader()
            // Exchange::createShare()
            // Exchange::assignShareToTrader()
        foreach ($share->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }

        // add share to traders share collection
        $trader->addShare($share);
        foreach ($trader->dispatchableEvents() as $event) {
            $this->messageBus->dispatch($event);
        }


        // get trader by id
        /** @var Trader $trader */
        $trader = $this->queryHandlerBus->query(new GetTraderByIdQuery($traderId));

        Kint::dump($trader);die;

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
