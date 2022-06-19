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
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'smaller-aggregate-domain-test',
    description: 'Add a short description for your command',
)]
class SmallerAggregateDomainTestCommand extends Command
{
    use HandleTrait;

    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    public function __construct(
        MessageBusInterface $messageBus,
        ExchangeReadRepositoryInterface $exchangeReadRepository
    )
    {
        $this->messageBus = $messageBus;
        $this->exchangeReadRepository = $exchangeReadRepository;

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

        // we can fake this and only have one exchange for now
        // if we had more than one exchange every others context
        // would need to pay attention to the exchange ID
        /** @var Exchange $exchange */
        $exchange = $this->handle(
            new CreateExchangeCommand(Uuid::uuid4())
        );

        $exchange = $this->exchangeReadRepository->findExchangeById($exchange->id()->toString());

        /** @var Share $share */
        $share = $this->handle(
            new CreateShareCommand(
                $exchange->id(),
                Uuid::uuid4(),
                Symbol::fromValue('FOO')
            )
        );

        /** @var Trader $traderA */
        $traderA = $this->handle(
            new CreateTraderCommand(
                $exchange->id(),
                Uuid::uuid4()
            )
        );
        /** @var Trader $traderB */
        $traderB = $this->handle(
            new CreateTraderCommand(
                $exchange->id(),
                Uuid::uuid4()
            )
        );

        $share = $this->handle(
            new TransferOwnershipToTraderCommand(
                $exchange->id(),
                $share->id(),
                $traderA->id()
            )
        );

//        dd($exchange, $share, $traderA, $traderB, $shareWithOwner);

        // exchange needs to listen to the event emitted by this aggregate
        /** @var Bid $bid */
        $bid = $this->handle(
            new CreateBidCommand(
                $exchange->id(),
                Uuid::uuid4(),
                $traderB->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(10)
            )
        );

        // exchange needs to listen to the event emitted by this aggregate
        /** @var Ask $ask */
        $ask = $this->handle(
            new CreateAskCommand(
                $exchange->id(),
                Uuid::uuid4(),
                $traderA->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(10)
            )
        );

        // the exchange should then execute the trade
        $this->handle(
            new AddAskToExchangeCommand(
                $exchange->id(),
                $ask->id(),
                $ask->traderId(),
                $ask->symbol(),
                $ask->price()
            )
        );

        $this->handle(
            new AddBidToExchangeCommand(
                $exchange->id(),
                $bid->id(),
                $bid->traderId(),
                $bid->symbol(),
                $bid->price()
            )
        );

        // the share context needs to listen to the exchange aggregate to update ownership
        $share = $this->handle(
            new TransferOwnershipToTraderCommand(
                $exchange->id(),
                $share->id(),
                $traderB->id()
            )
        );

//        dd($exchange, $share, $traderA, $traderB);

        // the bidAsk context needs to listen to the exchange to remove the bid and ask
        $this->handle(
            new RemoveAskCommand(
                $exchange->id(),
                $ask->id()
            )
        );
        $this->handle(
            new RemoveBidCommand(
                $exchange->id(),
                $bid->id()
            )
        );

        dump($exchange->trades());

        $io->success('Winner!');

        return Command::SUCCESS;
    }
}
