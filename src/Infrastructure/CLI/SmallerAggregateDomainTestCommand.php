<?php

namespace StockExchange\Infrastructure\CLI;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\BidAsk\Command\CreateAskCommand;
use StockExchange\Application\BidAsk\Command\CreateBidCommand;
use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\Application\Exchange\Command\AddAskToExchangeCommand;
use StockExchange\Application\Exchange\Command\AddBidToExchangeCommand;
use StockExchange\Application\Exchange\Command\CreateExchangeCommand;
use StockExchange\Application\Share\Command\CreateShareCommand;
use StockExchange\Application\Share\Command\TransferOwnershipToTraderCommand;
use StockExchange\Application\Trader\Command\CreateTraderCommand;
use StockExchange\StockExchange\BidAsk\Ask;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Exchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Share\Share;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader\Trader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'sadt', // smaller-aggregate-domain-test
    description: 'Add a short description for your command',
)]
class SmallerAggregateDomainTestCommand extends Command
{
    use HandleTrait;

    private ExchangeReadRepositoryInterface $exchangeReadRepository;
    private ParameterBagInterface $params;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository,
        ParameterBagInterface $params,
    )
    {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;
        $this->params = $params;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // TODO: add exchange entity to all other contexts (that refer to an exchange id)
        // this is because there COULD be multiple exchanges
        // for now we are assuming there is one

        $exchangeId = Uuid::fromString(
            $this->params->get('stock_exchange.default_exchange_id')
        );

        $exchangeId = Uuid::uuid4();

        /** @var Exchange $exchange */
        $this->handle(
            new CreateExchangeCommand($exchangeId)
        );

        /** @var Share $share */
        $share = $this->handle(
            new CreateShareCommand(
                $exchangeId,
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            )
        );

        /** @var Trader $traderA */
        $traderA = $this->handle(
            new CreateTraderCommand(
                $exchangeId,
                Uuid::uuid4()
            )
        );
        /** @var Trader $traderB */
        $traderB = $this->handle(
            new CreateTraderCommand(
                $exchangeId,
                Uuid::uuid4()
            )
        );

       $this->handle(
            new TransferOwnershipToTraderCommand(
                $exchangeId,
                $share->id(),
                $traderA->id()
            )
        );

        $this->handle(
            new AddAskToExchangeCommand(
                $exchangeId,
                Uuid::uuid4(),
                $traderA->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(10)
            )
        );

        $this->handle(
            new AddBidToExchangeCommand(
                $exchangeId,
                Uuid::uuid4(),
                $traderB->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(10)
            )
        );

        $io->success('Winner!');

        return Command::SUCCESS;
    }
}
