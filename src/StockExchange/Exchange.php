<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Exception;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Exchange\AskAddedToExchange;
use StockExchange\StockExchange\Event\Exchange\BidAddedToExchange;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\Exchange\ExchangeCreated;
use StockExchange\StockExchange\Event\Exchange\AskRemovedFromExchange;
use StockExchange\StockExchange\Event\Exchange\BidRemovedFromExchange;
use StockExchange\StockExchange\Event\Exchange\ShareAllocatedToTrader;
use StockExchange\StockExchange\Event\Exchange\ShareAddedToExchange;
use StockExchange\StockExchange\Event\Exchange\TradeExecuted;
use StockExchange\StockExchange\Event\Exchange\TraderAddedToExchange;
use StockExchange\StockExchange\Exception\AskCollectionCreationException;
use StockExchange\StockExchange\Exception\BidCollectionCreationException;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;
use StockExchange\StockExchange\Exception\StateRestorationException;
use StockExchange\StockExchange\Exception\TradeCollectionCreationException;

class Exchange implements DispatchableEventsInterface, \JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private SymbolCollection $symbols;
    private BidCollection $bids; // TODO: move this to an orderbook class
    private AskCollection $asks;  // TODO: move this to an orderbook class
    private TradeCollection $trades;
    private TraderCollection $traders;
    private ShareCollection $shares;
    /**
     * @var EventInterface[]
     */
    private array $appliedEvents = [];

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

     * @return Exchange
     */
    public static function create(UuidInterface $id): self {
        $exchange = new self();
        $exchange->id = $id;

        // TODO: initialise all of these with empty collections.
        // when an exchange is created nothing exists.
        // each of these will be added via other state
        // changes (events) after exchange creation
        $exchange->symbols = new SymbolCollection([]);
        $exchange->bids = new BidCollection([]);
        $exchange->asks = new AskCollection([]);
        $exchange->trades = new TradeCollection([]);
        $exchange->traders = new TraderCollection([]);
        $exchange->shares = new ShareCollection([]);

        $exchangeCreated = new ExchangeCreated($exchange);
        $exchangeCreated = $exchangeCreated->withMetadata($exchange->eventMetaData());
        $exchange->addDispatchableEvent($exchangeCreated);

        return $exchange;
    }

    /**
     * @param EventInterface[] $events
     * @return Exchange
     * @throws AskCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws StateRestorationException
     * @throws TradeCollectionCreationException
     */
    public static function restoreStateFromEvents(array $events): Exchange
    {
        $exchange = new self();

        foreach ($events as $event) {
            if (!is_a($event, EventInterface::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that implement EventInterface.'
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

                case is_a($event, TraderAddedToExchange::class):
                    $exchange->applyTraderAddedToExchange($event);
                    break;

                case is_a($event, ShareAddedToExchange::class):
                    $exchange->applyShareAddedToExchange($event);
                    break;

                case is_a($event, ShareAllocatedToTrader::class):
                    $exchange->applyShareAllocatedToTrader($event);
                    break;

                // TODO:

                // the event name we used here is already in use.
                // - think of another name?
                // - use the same event in two different entities?

//                case is_a($event, ShareOwnershipTransferred::class):
//                    $exchange->applyTraderAddedToExchange($event);
//                    break;
            }
        }

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
     * @return SymbolCollection
     */
    public function symbols(): SymbolCollection
    {
        return $this->symbols;
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
     * @return TraderCollection
     */
    public function traders(): TraderCollection
    {
        return $this->traders;
    }

    /**
     * @return ShareCollection
     */
    public function shares(): ShareCollection
    {
        return $this->shares;
    }

    /**
     * @return EventInterface[]
     */
    public function appliedEvents(): array
    {
        return $this->appliedEvents;
    }

    /**
     * @param UuidInterface $id
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @throws AskCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws ShareCollectionCreationException
     * @throws TradeCollectionCreationException
     */
    public function bid(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): void {
        // TODO: check symbol exists in symbol collection

        // ensure we have the current state of the trader on the exchange
        $trader = $this->traders()->findById($trader->id());

        // create the bid
        $bid = Bid::create($id, $trader, $symbol, $price);

        // add bid to collection
        $this->bids = new BidCollection($this->bids()->toArray() + [$bid]);

        $bidAdded = new BidAddedToExchange($bid);
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
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @throws AskCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws ShareCollectionCreationException
     * @throws TradeCollectionCreationException
     */
    public function ask(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): void {
        // TODO: check symbol exists in symbol collection

        // TODO: check the trader actually has shares of the given symbol to trade

        // TODO: ensure a trader can not create more asks than shares they have available tp trae
        // e.g 10 FOO's = 10 max asks for FOO for that trader

        // ensure we have the current state of the trader on the exchange
        $trader = $this->traders()->findById($trader->id());

        //create the ask
        $ask = Ask::create($id, $trader, $symbol, $price);

        // add ask to collection
        $this->asks = new AskCollection($this->asks()->toArray() + [$ask]);

        $askAdded = new AskAddedToExchange($ask);
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

    public function createTrader(UuidInterface $traderId)
    {
        $trader = Trader::create($traderId);

        $this->traders = new TraderCollection($this->traders()->toArray() + [$trader]);

        $traderAdded = new TraderAddedToExchange($trader);
        $traderAdded = $traderAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($traderAdded);
    }

    public function createShare(UuidInterface $shareId, Symbol $symbol)
    {
        $share = Share::create($shareId, $symbol);

        $this->shares = new ShareCollection($this->shares()->toArray() + [$share]);

        $shareAdded = new ShareAddedToExchange($share);
        $shareAdded = $shareAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($shareAdded);
    }

    /**
     * This method is used to allocate a share that has not yet been traded
     * to a trader. This occurs when new shares become available to the exchange.
     *
     * @param Share $share
     * @param Trader $trader
     */
    public function allocateShareToTrader(Share $share, Trader $trader)
    {
        // TODO: validate that this share has not been previously traded

        // TODO: validate that the share and trader match ones known to the exchange

//        dd($this->shares, $share);

        if(!$this->shares()->match($share)) {
            throw new Exception('shiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiit');
        }

        if(!$this->traders()->match($trader)) {
            throw new Exception('shiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiit');
        }

        // transfer ownership of the share to the trader
        $share->transferOwnershipToTrader($trader);

        // update share in share collection
        $this->shares()->removeShare($share->id());
        $this->shares = new ShareCollection($this->shares()->toArray() + [$share]);

        // add share to traders share collection
        $trader->addShare($share);

        // update trader in trader collection
        $this->traders()->removeTrader($trader->id());
        $this->traders = new TraderCollection($this->traders()->toArray() + [$trader]);

        $shareAllocated = new ShareAllocatedToTrader($share, $trader);
        $shareAllocated = $shareAllocated->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($shareAllocated);
    }

    /**
     * @return array{
     * id: string,
     * symbols: SymbolCollection,
     * bids: BidCollection,
     * asks: AskCollection,
     * trades: TradeCollection
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->asArray();
    }

    /**
     * @return array{
     * id: string,
     * symbols: SymbolCollection,
     * bids: BidCollection,
     * asks: AskCollection,
     * trades: TradeCollection
     * }
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'symbols' => $this->symbols()->toArray(),
            'bids' => $this->bids()->toArray(),
            'asks' => $this->asks()->toArray(),
            'trades' => $this->trades()->toArray(),
        ];
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

    private function aggregateVersion(): int
    {
        if (count($this->appliedEvents)) {
            /** @var DomainEvent $lastEvent */
            $lastEvent = end($this->appliedEvents);

            return $lastEvent->metadata()['_aggregate_version'];
        }

        return 0;
    }

    private function nextAggregateVersion(): int
    {
        // TODO: make this change to all other nextAggregateVersion methods
        // also find a nice way to count this
        $unDispatchedCount = 0;
        foreach ($this->dispatchableEvents() as $de) {
            if (str_contains(get_class($de), 'StockExchange\StockExchange\Event\Exchange')) {
                $unDispatchedCount++;
            }
        }

        return $this->aggregateVersion() + $unDispatchedCount + 1;
    }

    /**
     * @param Bid $bid
     * @param Ask $ask
     *
     * @throws AskCollectionCreationException
     * @throws BidCollectionCreationException
     * @throws TradeCollectionCreationException
     * @throws ShareCollectionCreationException
     */
    private function trade(Bid $bid, Ask $ask): void
    {
//        $bidTrader = $this->traders()->toArray()[$bid->trader()->id()->toString()];
//        $askTrader = $this->traders()->toArray()[$ask->trader()->id()->toString()];
        // execute the trade between the buyer and the seller

        // filter the share collection based on owner id and symbol
//        $askerShares = $this->shares()->filterByOwnerId($ask->trader()->id())->filterBySymbol($ask->symbol());


        // find one of the sellers shares, update the ownership of the share to the buyer
        /** @var Share $share */
        $share = current(
            $ask->trader()->shares()->filterBySymbol($ask->symbol())->toArray()
        ); // TODO: some proper error checking

//        $share = current($askerShares->toArray()); // TODO: some proper error checking

        $this->transferShare($share, $ask->trader(), $bid->trader());

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
     * @param Share  $share
     * @param Trader $seller
     * @param Trader $buyer
     *
     * @throws ShareCollectionCreationException
     */
    private function transferShare(Share $share, Trader $seller, Trader $buyer): void
    {
        // add share to buyers share collection
        $buyer->addShare($share);

        // remove share from sellers share collection
        $seller->removeShare($share);

        // update the shares owner id
        $share->transferOwnershipToTrader($buyer);
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

    /**
     * @param EventInterface $event
     */
    private function addAppliedEvent(EventInterface $event): void
    {
        $this->appliedEvents[] = $event;
    }

    /**
     * @param ExchangeCreated $event
     */
    private function applyExchangeCreated(ExchangeCreated $event): void
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->symbols = new SymbolCollection([]);
        $this->bids = new BidCollection([]);
        $this->asks = new AskCollection([]);
        $this->trades = new TradeCollection([]);
        $this->shares = new ShareCollection([]);
        $this->traders = new TraderCollection([]);

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
            Uuid::fromString($event->payload()['id']),
            $this->traders()->toArray()[$event->payload()['trader']['id']],
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
        unset($bids[$event->bid()->id()->toString()]);

        $this->bids = new BidCollection($bids);

        $this->addAppliedEvent($event);
    }

    /**
     * @param AskAddedToExchange $event
     * @throws AskCollectionCreationException
     */
    private function applyAskAddedToExchange(AskAddedToExchange $event): void
    {
//        $this->asks = new AskCollection($this->asks()->toArray() + [$event->ask()]);

        $this->asks = new AskCollection(
            $this->asks()->toArray() + [
                Ask::restoreFromValues(
                    Uuid::fromString($event->payload()['id']),
                    // using the trader that already exists in the exchanges collection
                    // TODO: this idea could be reused all over the place!
                    $this->traders()->toArray()[$event->payload()['trader']['id']],
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
        unset($asks[$event->ask()->id()->toString()]);

        $this->asks = new AskCollection($asks);

        $this->addAppliedEvent($event);
    }

    /**
     * @param TradeExecuted $event
     * @throws TradeCollectionCreationException
     */
    private function applyTradeExecuted(TradeExecuted $event): void
    {
        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$event->trade()]
        );

        $this->addAppliedEvent($event);
    }

    private function applyTraderAddedToExchange(TraderAddedToExchange $event): void
    {
        // TODO: why am I doing this?
        // create a new trader entity from the id in the payload
        // then restore that same entity from the entities dispatchable events????
        // it doesn't mak any sense!
        // either add a Trader::restoreFromValues method and pass in the payload values
        // or somehow get the traders events from storage and restore the state from the events
        // the first option is the best one (and only one really).
        // we might be better off dropping all events for all entities that are not teh aggregate root
        // (which I think is the way it should be anyway) it is going to cause a lot of problems if we
        // keep them. for instance you would not be able to change the state of a non-root entity after
        // it had originally been created because you would loose the aggregate version number
        // ---
        // see the method below - using restoreStateFromEvents in this fashion is a hack for now...
        $trader = Trader::create(Uuid::fromString($event->payload()['id']));

        $this->traders = new TraderCollection($this->traders()->toArray() + [$trader]);

        $this->addAppliedEvent($event);
    }

    private function applyShareAddedToExchange(ShareAddedToExchange $event)
    {
        // create the share again
        $share = Share::create(
            Uuid::fromString($event->payload()['id']),
            Symbol::fromValue($event->payload()['symbol'])
        );

        // add it to the collection
        $this->shares = new ShareCollection($this->shares()->toArray() + [$share]);

        $this->addAppliedEvent($event);
    }

    private function applyShareAllocatedToTrader(ShareAllocatedToTrader $event)
    {
        $share = $this->shares()->toArray()[$event->payload()['share']['id']];
        $trader = $this->traders()->toArray()[$event->payload()['trader']['id']];

        // transfer ownership of the share to the trader
        $share->transferOwnershipToTrader($trader);

        // update share in share collection
        $this->shares()->removeShare($share->id());
        $this->shares = new ShareCollection($this->shares()->toArray() + [$share]);

        // add share to traders share collection
        $trader->addShare($share);


        // update trader in trader collection
        $this->traders()->removeTrader($trader->id());
        $this->traders = new TraderCollection($this->traders()->toArray() + [$trader]);

        $this->addAppliedEvent($event);
    }
}
