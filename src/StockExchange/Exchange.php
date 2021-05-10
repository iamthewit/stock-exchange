<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\AskAddedToExchange;
use StockExchange\StockExchange\Event\AskCreated;
use StockExchange\StockExchange\Event\BidAddedToExchange;
use StockExchange\StockExchange\Event\EventInterface;

class Exchange
{
    private SymbolCollection $symbols;
    private BidCollection $bids; // TODO: move this to an orderbook class
    private AskCollection $asks;  // TODO: move this to an orderbook class
    private TradeCollection $trades;
    private array $dispatchableEvents = [];

    /**
     * Exchange constructor.
     */
    private function __construct()
    {
    }

    /**
     * Open the exchange so that shares can be traded.
     *
     * @param SymbolCollection $symbols
     * @param BidCollection    $bids
     * @param AskCollection    $asks
     * @param TradeCollection  $trades
     *
     * @return Exchange
     */
    public static function create(
        SymbolCollection $symbols,
        BidCollection $bids,
        AskCollection $asks,
        TradeCollection $trades
    ): self
    {
        $exchange = new self();
        $exchange->symbols = $symbols;
        $exchange->bids = $bids;
        $exchange->asks = $asks;
        $exchange->trades = $trades;

        return $exchange;
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

    public function dispatchableEvents(): array
    {
        return $this->dispatchableEvents;
    }

    /**
     * @param UuidInterface $id
     * @param Trader $trader
     * @param Symbol $symbol
     * @param Price $price
     * @throws Exception\AskCollectionCreationException
     * @throws Exception\BidCollectionCreationException
     * @throws Exception\ShareCollectionCreationException
     * @throws Exception\TradeCollectionCreationException
     */
    public function bid(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    )
    {
        //create the bid
        $bid = Bid::create($id, $trader, $symbol, $price);

        foreach ($bid->dispatchableEvents() as $event) {
            $this->addDispatchableEvent($event);
        }

        // add bid to collection
        $this->bids = new BidCollection($this->bids()->toArray() + [$bid]);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check ask collection for any matching asks
        $asks = $this->asks()->filterBySymbolAndPrice($bid->symbol(), $bid->price());

        if(count($asks)) {
            // TODO: implement a proper way of determining which ask
            // to pick for the trade if there is more than 1 available
            $chosenAsk = current($asks->toArray());

            // if match found execute a trade
            $this->trade($bid, $chosenAsk);
        }

        $bidAdded = new BidAddedToExchange($bid);
        $this->addDispatchableEvent($bidAdded);
    }

    /**
     * @param UuidInterface $id
     * @param Trader $trader
     * @param Symbol $symbol
     * @param Price $price
     * @throws Exception\AskCollectionCreationException
     * @throws Exception\BidCollectionCreationException
     * @throws Exception\ShareCollectionCreationException
     * @throws Exception\TradeCollectionCreationException
     */
    public function ask(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    )
    {
        //create the ask
        $ask = Ask::create($id, $trader, $symbol, $price);

        foreach ($ask->dispatchableEvents() as $event) {
            $this->addDispatchableEvent($event);
        }

        // add ask to collection
        $this->asks = new AskCollection($this->asks()->toArray() + [$ask]);

        // TODO: instead of executing trades on the bid/ask method
        // create another method that checks all bid/asks and executes
        // any trades possible

        // check bid collection for any matching bids
        $bids = $this->bids()->filterBySymbolAndPrice($ask->symbol(), $ask->price());

        // if match found execute trade
        if (count($bids)) {
            $chosenBid = current($bids->toArray());

            $this->trade($chosenBid, $ask);
        }

        $askCreated = new AskAddedToExchange($ask);
        $this->addDispatchableEvent($askCreated);
    }

    /**
     * @param Bid $bid
     * @param Ask $ask
     *
     * @throws Exception\AskCollectionCreationException
     * @throws Exception\BidCollectionCreationException
     * @throws Exception\TradeCollectionCreationException
     * @throws Exception\ShareCollectionCreationException
     */
    private function trade(Bid $bid, Ask $ask)
    {
        // execute the trade between the buyer and the seller
        $this->trades = new TradeCollection(
            $this->trades()->toArray() + [
                Trade::fromBidAndAsk(Uuid::uuid4(), $bid, $ask)
            ]
        );

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
    }

    /**
     * @param Share  $share
     * @param Trader $seller
     * @param Trader $buyer
     *
     * @throws Exception\ShareCollectionCreationException
     */
    private function transferShare(Share $share, Trader $seller, Trader $buyer)
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
     * @throws Exception\BidCollectionCreationException
     */
    private function removeBid(Bid $bid)
    {
        $bids = $this->bids()->toArray();
        unset($bids[$bid->id()->toString()]);

        $this->bids = new BidCollection($bids);
    }

    /**
     * @param Ask $ask
     *
     * @throws Exception\AskCollectionCreationException
     */
    private function removeAsk(Ask $ask)
    {
        $asks = $this->asks()->toArray();
        unset($asks[$ask->id()->toString()]);

        $this->asks = new AskCollection($asks);
    }

    private function addDispatchableEvent(EventInterface $event)
    {
        $this->dispatchableEvents[] = $event;
    }
}