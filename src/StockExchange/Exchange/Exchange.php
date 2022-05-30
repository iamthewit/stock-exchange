<?php


namespace StockExchange\StockExchange\Exchange;

use Exception;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exchange\Event\AskAdded;
use StockExchange\StockExchange\Exchange\Event\AskRemovedFromExchange;
use StockExchange\StockExchange\Exchange\Event\BidAdded;
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
        // TODO
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

        $bidAdded = new BidAdded($bid);
        $bidAdded = $bidAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($bidAdded);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check ask collection for any matching asks
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

        $askAdded = new AskAdded($ask);
        $askAdded = $askAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($askAdded);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check bid collection for any matching bids
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
        // remove bid from collection
        $this->removeBid($bid);

        // remove ask from collection
        $this->removeAsk($ask);

        // add trade to collection
        $trade = Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask);

        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$trade]
        );

        $tradeExecuted = new TradeExecuted($trade);
        $tradeExecuted = $tradeExecuted->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($tradeExecuted);
    }

    /**
     * @param Bid $bid
     *
     * @throws BidCollectionCreationException
     */
    private function removeBid(Bid $bid): void
    {
        $bids = $this->bids()->toArray();
        unset($bids[$bid->id()->toString()]);

        $this->bids = new BidCollection($bids);

        $bidRemovedFromExchange = new BidRemovedFromExchange($bid);
        $bidRemovedFromExchange = $bidRemovedFromExchange->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($bidRemovedFromExchange);
    }

    /**
     * @param Ask $ask
     *
     * @throws AskCollectionCreationException
     */
    private function removeAsk(Ask $ask): void
    {
        $asks = $this->asks()->toArray();
        unset($asks[$ask->id()->toString()]);

        $this->asks = new AskCollection($asks);

        $askRemovedFromExchange = new AskRemovedFromExchange($ask);
        $askRemovedFromExchange = $askRemovedFromExchange->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($askRemovedFromExchange);
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
}