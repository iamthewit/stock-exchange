<?php

namespace StockExchange\Infrastructure\CLI;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\BidAsk\Ask;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Exchange\Exchange;
use StockExchange\StockExchange\Exchange\Trade;
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
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'smaller-aggregate-domain-test',
    description: 'Add a short description for your command',
)]
class SmallerAggregateDomainTestCommand extends Command
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;

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
        $exchange = Exchange::create(Uuid::uuid4());

        $share = Share::create(
            Uuid::uuid4(),
            Symbol::fromValue('FOO')
        );

        $traderA = Trader::create(Uuid::uuid4());
        $traderB = Trader::create(Uuid::uuid4());

        $share->transferOwnershipToTrader($traderA->id());

        // exchange needs to listen to the event emitted by this aggregate
        $bid = Bid::create(
            Uuid::uuid4(),
            $traderB->id(),
            Symbol::fromValue('FOO'),
            Price::fromValue(10)
        );

        // exchange needs to listen to the event emitted by this aggregate
        $ask = Ask::create(
            Uuid::uuid4(),
            $traderA->id(),
            Symbol::fromValue('FOO'),
            Price::fromValue(10)
        );

        // the exchange should then execute the trade
        $exchange->ask(
            $ask->id(),
            $ask->traderId(),
            $ask->symbol(),
            $ask->price()
        );
        $exchange->bid(
            $bid->id(),
            $bid->traderId(),
            $bid->symbol(),
            $bid->price()
        );

        // the share context needs to listen to the exchange aggregate to update ownership
        $share->transferOwnershipToTrader($traderB->id());

        // the bidAsk context needs to listen to the exchange to remove the bid and ask
        $ask->remove();
        $bid->remove();

        dump($exchange->trades());

        $io->success('Winner!');

        return Command::SUCCESS;
    }
}
