<?php

namespace StockExchange\Infrastructure\CLI;

use StockExchange\StockExchange\BidAsk\Ask;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Exchange\Exchange;
use StockExchange\StockExchange\Share\Share;
use StockExchange\StockExchange\Trader\Trader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'smaller-aggregate-domain-test',
    description: 'Add a short description for your command',
)]
class SmallerAggregateDomainTestCommand extends Command
{
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

        $exchange = Exchange::create();

        $share = Share::create();

        $traderA = Trader::create();
        $traderB = Trader::create();

        $share->transferOwnershipToTrader();

        // exchange needs to listen to the event emitted by this aggregate
        $bid = Bid::create();
        // exchange needs to listen to the event emitted by this aggregate
        $ask = Ask::create();

        // the exchange should then execute the trade
        $exchange->ask();
        $exchange->bid();

        // the share context needs to listen to the exchange aggregate to update ownership
        $share->transferOwnershipToTrader();

        // the bidAsk context needs to listen to the exchange to remove the bid and ask
        $ask->remove();
        $bid->remove();

        $io->success('Winner!');

        return Command::SUCCESS;
    }
}
