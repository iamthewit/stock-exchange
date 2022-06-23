<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\Application\Exchange\Command\AddAskToExchangeCommand;
use StockExchange\Application\Exchange\Command\AddBidToExchangeCommand;
use StockExchange\Application\Message\GenericMessage;
use Ramsey\Uuid\Uuid;
use StockExchange\Application\Share\Command\TransferOwnershipToTraderCommand;
use StockExchange\StockExchange\Event\Exchange\ExchangeCreated;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Class GenericMessageHandler
 * @package ExchangeReport\Application\Handler
 */
class GenericMessageHandler implements MessageHandlerInterface
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
    }

    public function __invoke(GenericMessage $genericMessage)
    {
        /** BidAsk Events */
        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\AskAdded") {
            dump('handling it');
            $this->handle(
                new AddAskToExchangeCommand(
                    Uuid::fromString($genericMessage->payload()['payload']['exchangeId']),
                    Uuid::fromString($genericMessage->payload()['payload']['id']),
                    Uuid::fromString($genericMessage->payload()['payload']['traderId']),
                    Symbol::fromValue($genericMessage->payload()['payload']['symbol']['value']),
                    Price::fromValue($genericMessage->payload()['payload']['price']['value'])
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\BidAdded") {
            dump('handling it');
            $this->handle(
                new AddBidToExchangeCommand(
                    Uuid::fromString($genericMessage->payload()['payload']['exchangeId']),
                    Uuid::fromString($genericMessage->payload()['payload']['id']),
                    Uuid::fromString($genericMessage->payload()['payload']['traderId']),
                    Symbol::fromValue($genericMessage->payload()['payload']['symbol']['value']),
                    Price::fromValue($genericMessage->payload()['payload']['price']['value'])
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\AskRemoved") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\BidRemoved") {
            // do something?
        }
        /** --- */

        /** Exchange Events */
        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\AskAddedToExchange") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\AskRemovedFromExchange") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\BidAddedToExchange") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\BidRemovedFromExchange") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\ExchangeCreated") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Event\Exchange\TradeExecuted") {
            // remove the bid
//            $this->handle(
//                new RemoveBidCommand(
//                    // TODO: no access to meta data??? why not???
//                    Uuid::fromString($event->metadata()['_aggregate_id']), // the trade is part of the exchange aggregate - so the aggregate id here is the exchange id
//                    Uuid::fromString($event->payload()['bid']['bidId'])
//                )
//            );

            // remove the ask
//            $this->handle(
//                new RemoveAskCommand(
//                    Uuid::fromString($event->metadata()['_aggregate_id']), // the trade is part of the exchange aggregate - so the aggregate id here is the exchange id
//                    Uuid::fromString($event->payload()['ask']['askId'])
//                )
//            );

            // transfer the ownership of that share
//            $shareIds = $this->exchangeReadRepository->findShareIdsBySymbolAndTraderId(
//                Symbol::fromValue($event->payload()['ask']['symbol']['value']),
//                Uuid::fromString($event->payload()['ask']['traderId'])
//            );

//            $share = $this->exchangeReadRepository->findShareById($shareIds[array_rand($shareIds)]);

            // Transfer share from ASKer to BIDer
//            $this->handle(
//                new TransferOwnershipToTraderCommand(
//                    Uuid::fromString($event->metadata()['_aggregate_id']),
//                    $share->id(),
//                    Uuid::fromString($event->payload()['bid']['traderId'])
//                )
//            );
        }
        /** --- */

        /** Share Events */
        if ($genericMessage->type() === "StockExchange\StockExchange\Share\Event\ShareCreated") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Share\Event\ShareOwnershipTransferred") {
            // do something?
        }
        /** --- */

        /** Trader Events */
        if ($genericMessage->type() === "StockExchange\StockExchange\Trader\Event\TraderCreated") {
            // do something?
        }
        /** --- */

        dump($genericMessage);
    }
}