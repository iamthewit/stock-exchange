<?php


namespace StockExchange\StockExchange\Exchange;

use Exception;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exception\StateRestorationException;
use StockExchange\StockExchange\Exchange\Event\AskAddedToExchange;
use StockExchange\StockExchange\Exchange\Event\AskRemovedFromExchange;
use StockExchange\StockExchange\Exchange\Event\BidAddedToExchange;
use StockExchange\StockExchange\Exchange\Event\BidRemovedFromExchange;
use StockExchange\StockExchange\Exchange\Event\ExchangeCreated;
use StockExchange\StockExchange\Exchange\Event\TradeExecuted;
use StockExchange\StockExchange\Exchange\Exception\AskCollectionCreationException;
use StockExchange\StockExchange\Exchange\Exception\BidCollectionCreationException;
use StockExchange\StockExchange\Exchange\Exception\TradeCollectionCreationException;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

/**
 * Class Exchange
 * @package StockExchange\StockExchange\Exchange
 */
class Exchange implements DispatchableEventsInterface, \JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private TradeCollection $trades;
    private BidCollection $bids; // TODO: move this to an orderbook class
    private AskCollection $asks;  // TODO: move this to an orderbook class

    /**
     * @var Event[]
     */
    private array $appliedEvents = [];
    private Event $lastAppliedEvent;

    /**
     * Exchange constructor.
     */
    private function __construct()
    {
    }

    /**
     * Open the exchange so that shares can be traded.
     *
     * @param UuidInterface $id
     *
     * @return Exchange
     * @throws BidCollectionCreationException
     * @throws AskCollectionCreationException
     * @throws TradeCollectionCreationException
     */
    public static function create(UuidInterface $id): self
    {
        $exchange = new self();
        $exchange->id = $id;
        $exchange->bids = new BidCollection([]);
        $exchange->asks = new AskCollection([]);
        $exchange->trades = new TradeCollection([]);

        $exchangeCreated = new ExchangeCreated($exchange);
        $exchangeCreated = $exchangeCreated->withMetadata($exchange->eventMetaData());
        $exchange->addDispatchableEvent($exchangeCreated);

        return $exchange;
    }

    public static function restoreStateFromEvents(array $events): Exchange
    {
        $exchange = new self();

        foreach ($events as $event) {
            if (!is_a($event, Event::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that extend the Event class.'
                );
            }

            switch ($event) {
                case is_a($event, ExchangeCreated::class):
                    $exchange->applyExchangeCreated($event);
                    break;

                case is_a($event, BidAddedToExchange::class):
                    $exchange->applyBidAddedToExchange($event);
                    break;

                case is_a($event, AskAddedToExchange::class):
                    $exchange->applyAskAddedToExchange($event);
                    break;

                case is_a($event, BidRemovedFromExchange::class):
                    $exchange->applyBidRemovedFromExchange($event);
                    break;

                case is_a($event, AskRemovedFromExchange::class):
                    $exchange->applyAskRemovedFromExchange($event);
                    break;

                case is_a($event, TradeExecuted::class):
                    $exchange->applyTradeExecuted($event);
                    break;
            }
        }

        return $exchange;
    }

    public static function restoreFromValues(array $result): Exchange
    {
        $exchange = new self();
        $exchange->id = Uuid::fromString($result['id']);

        return $exchange;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return BidCollection
     */
    public function bids(): BidCollection
    {
        return $this->bids;
    }

    /**
     * @return AskCollection
     */
    public function asks(): AskCollection
    {
        return $this->asks;
    }

    /**
     * @return TradeCollection
     */
    public function trades(): TradeCollection
    {
        return $this->trades;
    }

    /**
     * @param UuidInterface $id
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @throws BidCollectionCreationException
     * @throws AskCollectionCreationException
     * @throws TradeCollectionCreationException
     */
    public function bid(
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): void {
        // create the bid
        $bid = Bid::create($id, $traderId, $symbol, $price);

        // add bid to collection
        $this->bids = new BidCollection($this->bids()->toArray() + [$bid]);

        $bidAdded = new BidAddedToExchange($bid);
        $bidAdded = $bidAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($bidAdded);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check ask collection for any matching asks
        // TODO: filter out asks that are owned by the trader
        // who submitted the bid ($bid->traderId)
        $asks = $this->asks()->filterBySymbolAndPrice($bid->symbol(), $bid->price());

        if (count($asks)) {
            // TODO: implement a proper way of determining which ask
            // to pick for the trade if there is more than 1 available
            $chosenAsk = current($asks->toArray());

            if ($chosenAsk === false) {
                // TODO: sort this out properly
                throw new Exception('ruh roh');
            }

            // if match found execute a trade
            $this->trade($bid, $chosenAsk);
        }
    }

    /**
     * @param UuidInterface $id
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @throws AskCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws TradeCollectionCreationException
     */
    public function ask(
        UuidInterface $id,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): void {
        //create the ask
        $ask = Ask::create($id, $traderId, $symbol, $price);

        // add ask to collection
        $this->asks = new AskCollection($this->asks()->toArray() + [$ask]);

        $askAdded = new AskAddedToExchange($ask);
        $askAdded = $askAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($askAdded);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check bid collection for any matching bids
        // TODO: filter out bids that are owned by the trader
        // who submitted the ask ($ask->traderId)
        $bids = $this->bids()->filterBySymbolAndPrice($ask->symbol(), $ask->price());

        // if match found execute trade
        if (count($bids)) {
            $chosenBid = current($bids->toArray());

            if ($chosenBid === false) {
                // TODO: sort this out properly
                throw new Exception('ruh roh');
            }

            $this->trade($chosenBid, $ask);
        }
    }

    /**
     * @param UuidInterface $bidId
     *
     * @throws BidCollectionCreationException
     */
    public function removeBid(UuidInterface $bidId): void
    {
        $bids = $this->bids()->toArray();
        unset($bids[$bidId->toString()]);

        $this->bids = new BidCollection($bids);

        $bidRemovedFromExchange = new BidRemovedFromExchange($bidId);
        $bidRemovedFromExchange = $bidRemovedFromExchange->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($bidRemovedFromExchange);
    }

    /**
     * @param UuidInterface $askId
     *
     * @throws AskCollectionCreationException
     */
    public function removeAsk(UuidInterface $askId): void
    {
        $asks = $this->asks()->toArray();
        unset($asks[$askId->toString()]);

        $this->asks = new AskCollection($asks);

        $askRemovedFromExchange = new AskRemovedFromExchange($askId);
        $askRemovedFromExchange = $askRemovedFromExchange->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($askRemovedFromExchange);
    }

    /**
     * @return Event[]
     */
    public function appliedEvents(): array
    {
        return $this->appliedEvents;
    }

    public function lastAppliedEvent(): DomainEvent
    {
        return $this->lastAppliedEvent;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'trades' => $this->trades()->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{
     * _aggregate_id: string,
     * _aggregate_version: int,
     * _aggregate_type: string
     * }
     */
    protected function eventMetaData(): array
    {
        return [
            '_aggregate_id' => $this->id()->toString(),
            '_aggregate_version' => $this->nextAggregateVersion(),
            '_aggregate_type' => static::class
        ];
    }

    /**
     * @throws TradeCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws AskCollectionCreationException
     */
    private function trade(Bid $bid, Ask $ask): void
    {
        // TODO: the exchange should not be removing the bids
        // and asks immediately - it is not the exchanges responsibility
        // The bids and asks are owned by the BidAsk context, the exchange
        // needs to wait for the BidAsk context to remove the bid and ask before
        // it can remove them:
        // Exchange emits trade executed
        // BidAsk listens for trade executed
        // BidAsk removes Bid and emits event
        // BidAsk removes Ask and emits event
        // Exchange listens for Bid removed and Ask removed from BidAsk context
        // exchange removes bid and ask from it's own context

        // the exchange is not in charge of bids and asks!!!!

        // remove bid from collection
//        $this->removeBid($bid);

        // remove ask from collection
//        $this->removeAsk($ask);

        // add trade to collection
        $trade = Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask);

        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$trade]
        );

        $tradeExecuted = new TradeExecuted($trade);
        $tradeExecuted = $tradeExecuted->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($tradeExecuted);
    }

    private function aggregateVersion(): int
    {
        // TODO: make this nicer
        if (isset($this->lastAppliedEvent)) { // used for mongo read restore
            return $this->lastAppliedEvent->metadata()['_aggregate_version'];
        } elseif (count($this->appliedEvents())) { // used for mysql event store restore
            /** @var DomainEvent $lastEvent */
            $lastEvent = end($this->appliedEvents);

            return $lastEvent->metadata()['_aggregate_version'];
        }

        return 0;
    }

    private function nextAggregateVersion(): int
    {
        return $this->aggregateVersion() + 1;
    }

    /**
     * @param Event $event
     */
    private function addAppliedEvent(Event $event): void
    {
        $this->appliedEvents[] = $event;
    }

    /**
     * @param ExchangeCreated $event
     */
    private function applyExchangeCreated(ExchangeCreated $event): void
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->bids = new BidCollection([]);
        $this->asks = new AskCollection([]);
        $this->trades = new TradeCollection([]);

        $this->addAppliedEvent($event);
    }

    /**
     * @param BidAddedToExchange $event
     *
     * @throws BidCollectionCreationException
     */
    private function applyBidAddedToExchange(BidAddedToExchange $event): void
    {
        // ensure we have the current state of the trader on the exchange
        // create the bid
        $bid = Bid::create(
            Uuid::fromString($event->payload()['bidId']),
            Uuid::fromString($event->payload()['traderId']),
            Symbol::fromValue($event->payload()['symbol']['value']),
            Price::fromValue($event->payload()['price']['value'])
        );

        // add bid to collection
        $this->bids = new BidCollection($this->bids()->toArray() + [$bid]);

        $this->addAppliedEvent($event);
    }

    /**
     * @param BidRemovedFromExchange $event
     *
     * @throws BidCollectionCreationException
     */
    private function applyBidRemovedFromExchange(BidRemovedFromExchange $event): void
    {
        $bids = $this->bids()->toArray();
        unset($bids[$event->payload()['bidId']]);

        $this->bids = new BidCollection($bids);

        $this->addAppliedEvent($event);
    }

    /**
     * @param AskAddedToExchange $event
     * @throws AskCollectionCreationException
     */
    private function applyAskAddedToExchange(AskAddedToExchange $event): void
    {
        $this->asks = new AskCollection(
            $this->asks()->toArray() + [
                Ask::restoreFromValues(
                    Uuid::fromString($event->payload()['askId']),
                    Uuid::fromString($event->payload()['traderId']),
                    Symbol::fromValue($event->payload()['symbol']['value']),
                    Price::fromValue($event->payload()['price']['value'])
                )
            ]
        );

        $this->addAppliedEvent($event);
    }

    /**
     * @param AskRemovedFromExchange $event
     *
     * @throws AskCollectionCreationException
     */
    private function applyAskRemovedFromExchange(AskRemovedFromExchange $event): void
    {
        $asks = $this->asks()->toArray();
        unset($asks[$event->payload()['askId']]);

        $this->asks = new AskCollection($asks);

        $this->addAppliedEvent($event);
    }

    /**
     * @param TradeExecuted $event
     * @throws TradeCollectionCreationException
     */
    private function applyTradeExecuted(TradeExecuted $event): void
    {
        $trade = Trade::fromBidAndAsk(
            Uuid::fromString($event->payload()['tradeId']),
            Bid::restoreFromValues(
                Uuid::fromString($event->payload()['bid']['bidId']),
                Uuid::fromString($event->payload()['bid']['traderId']),
                Symbol::fromValue($event->payload()['bid']['symbol']['value']),
                Price::fromValue($event->payload()['bid']['price']['value'])
            ),
            Ask::restoreFromValues(
                Uuid::fromString($event->payload()['ask']['askId']),
                Uuid::fromString($event->payload()['ask']['traderId']),
                Symbol::fromValue($event->payload()['ask']['symbol']['value']),
                Price::fromValue($event->payload()['ask']['price']['value'])
            ),
        );
        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$trade]
        );

        $this->addAppliedEvent($event);
    }
}