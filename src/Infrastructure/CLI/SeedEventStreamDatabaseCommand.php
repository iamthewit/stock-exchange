<?php

namespace StockExchange\Infrastructure\CLI;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Command\CreateExchangeCommand;
use StockExchange\Application\Command\CreateShareCommand;
use StockExchange\Application\Command\CreateTraderCommand;
use StockExchange\StockExchange\Symbol;
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

    /**
     * SeedEventStreamDatabaseCommand constructor.
     *
     * @param ParameterBagInterface $params
     * @param MessageBusInterface   $messageBus
     */
    public function __construct(ParameterBagInterface $params, MessageBusInterface $messageBus)
    {
        $this->params = $params;
        $this->messageBus = $messageBus;

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
        $this->messageBus->dispatch(new CreateTraderCommand(Uuid::uuid4()));

        // get trader by id
            // TODO create query + handler for this

        // create some shares
        $shareId = Uuid::uuid4();
        $this->messageBus->dispatch(
            new CreateShareCommand(
                $shareId,
                Symbol::fromValue('FOO')
            )
        );

        // for each share
            // transfer ownership to trader
            // add share to traders share collection

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
