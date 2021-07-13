<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Iterator;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\AskAddedToExchange;
use StockExchange\StockExchange\Event\BidAddedToExchange;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\ExchangeCreated;
use StockExchange\StockExchange\Event\AskRemovedFromExchange;
use StockExchange\StockExchange\Event\BidRemovedFromExchange;
use StockExchange\StockExchange\Event\TradeExecuted;
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
     * @param SymbolCollection $symbols
     * @param BidCollection $bids
     * @param AskCollection $asks
     * @param TradeCollection $trades
     *
     * @return Exchange
     */
    public static function create(
        UuidInterface $id,
        SymbolCollection $symbols,
        BidCollection $bids,
        AskCollection $asks,
        TradeCollection $trades
    ): self {
        $exchange = new self();
        $exchange->id = $id;
        $exchange->symbols = $symbols;
        $exchange->bids = $bids;
        $exchange->asks = $asks;
        $exchange->trades = $trades;

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

        // create the bid
        $bid = Bid::create($id, $trader, $symbol, $price);

        // TODO: add event meta data to Bid object
//        foreach ($bid->dispatchableEvents() as $event) {
//            $this->addDispatchableEvent($event);
//        }

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
                throw new \Exception('ruh roh');
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

        //create the ask
        $ask = Ask::create($id, $trader, $symbol, $price);

        foreach ($ask->dispatchableEvents() as $event) {
            $this->addDispatchableEvent($event);
        }

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
                throw new \Exception('ruh roh');
            }

            $this->trade($chosenBid, $ask);
        }
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
            'symbols' => $this->symbols(),
            'bids' => $this->bids(),
            'asks' => $this->asks(),
            'trades' => $this->trades(),
        ];
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
        // execute the trade between the buyer and the seller

        // find one of the sellers shares, update the ownership of the share to the buyer
        /** @var Share $share */
        $share = current(
            $bid->trader()->shares()->filterBySymbol($bid->symbol())->toArray()
        ); // TODO: some proper error checking

        $this->transferShare($share, $bid->trader(), $ask->trader());

        // remove bid from collection
        $this->removeBid($bid);

        // remove ask from collection
        $this->removeAsk($ask);

        // add trade to collection
        $trade = Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask);

        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [$trade]
        );

        $event = new TradeExecuted($trade);
        $this->addDispatchableEvent($event);
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
        // remove share from sellers share collection
        $seller->removeShare($share);

        // add share to buyers share collection
        $buyer->addShare($share);

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

        $event = new BidRemovedFromExchange($bid);
        $this->addDispatchableEvent($event);
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

        $event = new AskRemovedFromExchange($ask);
        $this->addDispatchableEvent($event);
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

        // TODO: rebuild collections
//        $this->symbols = new SymbolCollection($event->payload()['symbols']);
//        $this->bids = new BidCollection($event->payload()['bids']);
//        $this->asks = new AskCollection($event->payload()['asks']);
//        $this->trades = new TradeCollection($event->payload()['trades']);

        $this->symbols = new SymbolCollection([]);
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
        $this->bids = new BidCollection($this->bids()->toArray() + [$event->bid()]);

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
        $this->asks = new AskCollection($this->asks()->toArray() + [$event->ask()]);

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
        return $this->aggregateVersion() + 1;
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
}
