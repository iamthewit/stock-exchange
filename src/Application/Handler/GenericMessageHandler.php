<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\BidAsk\Command\CreateAskCommand;
use StockExchange\Application\BidAsk\Command\CreateBidCommand;
use StockExchange\Application\BidAsk\Command\RemoveAskCommand;
use StockExchange\Application\BidAsk\Command\RemoveBidCommand;
use StockExchange\Application\Exchange\Command\AddAskToExchangeCommand;
use StockExchange\Application\Exchange\Command\AddBidToExchangeCommand;
use StockExchange\Application\Exchange\Command\RemoveAskFromExchangeCommand;
use StockExchange\Application\Exchange\Command\RemoveBidFromExchangeCommand;
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
            // do something???
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\BidAdded") {
            /// do something???
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\AskRemoved") {
            // do something???
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\BidAsk\Event\BidRemoved") {
            // do something???
        }
        /** --- */

        /** Exchange Events */
        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\AskAddedToExchange") {
            $this->handle(
                new CreateAskCommand(
                    Uuid::fromString($genericMessage->data()['metadata']['_aggregate_id']),
                    Uuid::fromString($genericMessage->data()['payload']['askId']),
                    Uuid::fromString($genericMessage->data()['payload']['traderId']),
                    Symbol::fromValue($genericMessage->data()['payload']['symbol']['value']),
                    Price::fromValue($genericMessage->data()['payload']['price']['value'])
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\AskRemovedFromExchange") {
            $this->handle(
                new RemoveAskCommand(
                    Uuid::fromString($genericMessage->data()['metadata']['_aggregate_id']),
                    Uuid::fromString($genericMessage->data()['payload']['askId']),
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\BidAddedToExchange") {
            $this->handle(
                new CreateBidCommand(
                    Uuid::fromString($genericMessage->data()['metadata']['_aggregate_id']),
                    Uuid::fromString($genericMessage->data()['payload']['bidId']),
                    Uuid::fromString($genericMessage->data()['payload']['traderId']),
                    Symbol::fromValue($genericMessage->data()['payload']['symbol']['value']),
                    Price::fromValue($genericMessage->data()['payload']['price']['value'])
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\BidRemovedFromExchange") {
            $this->handle(
                new RemoveBidCommand(
                    Uuid::fromString($genericMessage->data()['metadata']['_aggregate_id']),
                    Uuid::fromString($genericMessage->data()['payload']['bidId']),
                )
            );
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\ExchangeCreated") {
            // do something?
        }

        if ($genericMessage->type() === "StockExchange\StockExchange\Exchange\Event\TradeExecuted") {
            // transfer the ownership of the share
            $shareIds = $this->exchangeReadRepository->findShareIdsBySymbolAndTraderId(
                Symbol::fromValue($genericMessage->data()['payload']['ask']['symbol']['value']),
                Uuid::fromString($genericMessage->data()['payload']['ask']['traderId'])
            );

            $share = $this->exchangeReadRepository->findShareById($shareIds[array_rand($shareIds)]);

            // Transfer share from ASKer to BIDer
            $this->handle(
                new TransferOwnershipToTraderCommand(
                    Uuid::fromString($genericMessage->data()['metadata']['_aggregate_id']),
                    $share->id(),
                    Uuid::fromString($genericMessage->data()['payload']['bid']['traderId'])
                )
            );
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