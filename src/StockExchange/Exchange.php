<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Exception;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Event\Exchange\AskAddedToExchange;
use StockExchange\StockExchange\Event\Exchange\BidAddedToExchange;
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

     * @return Exchange
     */
    public static function create(UuidInterface $id): self {
        $exchange = new self();
        $exchange->id = $id;
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
     * @param Event[] $events
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

                case is_a($event, TraderAddedToExchange::class):
                    $exchange->applyTraderAddedToExchange($event);
                    break;

                case is_a($event, ShareAddedToExchange::class):
                    $exchange->applyShareAddedToExchange($event);
                    break;

                case is_a($event, ShareAllocatedToTrader::class):
                    $exchange->applyShareAllocatedToTrader($event);
                    break;
            }
        }

        return $exchange;
    }

    public static function restoreFromValues(array $result): Exchange
    {
        $exchange = new self();
        $exchange->id = Uuid::fromString($result['id']);

        $traders = [];
        foreach ($result['traders'] as $trader) {
            $shares = [];
            foreach ($trader['shares'] as $share) {
                $shares[] = Share::fromValues(
                    Uuid::fromString($share['id']),
                    Symbol::fromValue(
                        $share['symbol']
                    ),
                    Uuid::fromString($share['owner_id'])
                );
            }
            $traders[] = Trader::restoreFromValues(
                Uuid::fromString($trader['id']),
                new ShareCollection($shares)
            );
        }
        $exchange->traders = new TraderCollection($traders);

        $shares = [];
        foreach ($result['shares'] as $share) {
            $shares[] = Share::fromValues(
                Uuid::fromString($share['id']),
                Symbol::fromValue(
                    $share['symbol']
                ),
                $share['owner_id'] ? Uuid::fromString($share['owner_id']) : null
            );
        }
        $exchange->shares = new ShareCollection($shares);

        $asks = [];
        foreach ($result['asks'] as $ask) {
            $asks[] = Ask::restoreFromValues(
                Uuid::fromString($ask['id']),
                $exchange->traders()->findById(Uuid::fromString($ask['trader']['id'])),
                Symbol::fromValue(
                    $ask['symbol']
                ),
                Price::fromValue($ask['price'])
            );
        }
        $exchange->asks = new AskCollection($asks);

        $bids = [];
        foreach ($result['bids'] as $bid) {
            $bids[] = Bid::restoreFromValues(
                Uuid::fromString($bid['id']),
                $exchange->traders()->findById(Uuid::fromString($bid['trader']['id'])),
                Symbol::fromValue(
                    $bid['symbol']['value']
                ),
                Price::fromValue($bid['price']['value'])
            );
        }
        $exchange->bids = new BidCollection($bids);

        $trades = [];
        foreach ($result['trades'] as $trade) {
            $trades[] = Trade::fromBidAndAsk(
                Uuid::fromString($trade['id']),
                $exchange->bids()->findById(Uuid::fromString($trade['bid_id'])),
                $exchange->asks()->findById(Uuid::fromString($trade['ask_id']))
            );
        }
        $exchange->trades = new TradeCollection($trades);
        $exchange->symbols = new SymbolCollection([]); // TODO: at some point, maybe

        // TODO: find a nicer way to deal with this
        $result['last_applied_event']['created_at'] = new \DateTimeImmutable($result['last_applied_event']['created_at']);

        $exchange->lastAppliedEvent = Event::fromArray($result['last_applied_event']);

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
        return $this->toArray();
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
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'symbols' => $this->symbols()->toArray(),
            'bids' => $this->bids()->toArray(),
            'asks' => $this->asks()->toArray(),
            'trades' => $this->trades()->toArray(),
            'traders' => $this->traders()->toArray(),
            'shares' => $this->shares()->toArray(),
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
        // find one of the sellers shares, update the ownership of the share to the buyer
        /** @var Share $share */
        $share = current(
            $ask->trader()->shares()->filterBySymbol($ask->symbol())->toArray()
        ); // TODO: some proper error checking

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
        unset($bids[$event->payload()['id']]);

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
                    Uuid::fromString($event->payload()['id']),
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
        unset($asks[$event->payload()['id']]);

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
            Uuid::fromString($event->payload()['id']),
            Bid::restoreFromValues(
                Uuid::fromString($event->payload()['bid']['id']),
                $this->traders()->findById(Uuid::fromString($event->payload()['bid']['trader']['id'])),
                Symbol::fromValue($event->payload()['bid']['symbol']['value']),
                Price::fromValue($event->payload()['bid']['price']['value'])
            ),
            Ask::restoreFromValues(
                Uuid::fromString($event->payload()['ask']['id']),
                $this->traders()->findById(Uuid::fromString($event->payload()['ask']['trader']['id'])),
                Symbol::fromValue($event->payload()['ask']['symbol']['value']),
                Price::fromValue($event->payload()['ask']['price']['value'])
            ),
        );
        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$trade]
        );

        $this->addAppliedEvent($event);
    }

    private function applyTraderAddedToExchange(TraderAddedToExchange $event): void
    {
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
