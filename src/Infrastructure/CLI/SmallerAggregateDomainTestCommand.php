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
    name: 'smaller-aggregate-domain-test',
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

//        $exchangeId = Uuid::uuid4();

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

        $share = $this->handle(
            new TransferOwnershipToTraderCommand(
                $exchangeId,
                $share->id(),
                $traderA->id()
            )
        );

        // exchange needs to listen to the event emitted by this aggregate
        /** @var Bid $bid */
        $bid = $this->handle(
            new CreateBidCommand(
                $exchangeId,
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
                $exchangeId,
                Uuid::uuid4(),
                $traderA->id(),
                Symbol::fromValue('FOO'),
                Price::fromValue(10)
            )
        );

        // the exchange should then execute the trade
        $this->handle(
            new AddAskToExchangeCommand(
                $exchangeId,
                $ask->id(),
                $ask->traderId(),
                $ask->symbol(),
                $ask->price()
            )
        );

        $this->handle(
            new AddBidToExchangeCommand(
                $exchangeId,
                $bid->id(),
                $bid->traderId(),
                $bid->symbol(),
                $bid->price()
            )
        );

        // TradeExecutedListener transfers the ownership of a share from the asker to the bidder
        // in the Share context by listening to the event from the Exchange context

        // AskRemovedFromExchangeListener removes the ask from the BidAsk context
        // by listening for the AskRemovedFromExchange event from the exchange context

        // BidRemovedFromExchangeListener removes the bid from the BidAsk context
        // by listening for the BidRemovedFromExchange event from the exchange context

        // TODO: in the future these events will be coming via kafka event streams
        // at the moment they are happening internally within symfony
        // we need to refactor to listen to the events from kafka instead

        // TODO: the exchange context should also listen for the BidAsk remove events
        // as bids and asks could be removed by traders - if this happens the exchange
        // needs to update itself

        $exchange = $this->exchangeReadRepository->findExchangeById($exchangeId->toString());
        dump($exchange->trades());

        $io->success('Winner!');

        return Command::SUCCESS;
    }
}
