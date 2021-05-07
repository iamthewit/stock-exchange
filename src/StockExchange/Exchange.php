<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Kint\Kint;
use Ramsey\Uuid\Uuid;

class Exchange
{
    private SymbolCollection $symbols;
    private BidCollection $bids; // TODO: move this to an orderbook class
    private AskCollection $asks;  // TODO: move this to an orderbook class
    private TradeCollection $trades;

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
     * @param BidCollection $bids
     * @param AskCollection $asks
     * @param TradeCollection $trades
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

    public function bid(Bid $bid)
    {
        // add bid to collection
        $this->bids = new BidCollection($this->bids()->toArray() + [$bid]);

        // check ask collection for any matching asks
        $asks = $this->asks()->filterBySymbolAndPrice($bid->symbol(), $bid->price());

        if(count($asks)) {
            // TODO: implement a proper way of determining which ask
            // to pick for the trade if there is more than 1 available
            $chosenAsk = current($asks->toArray());

            // if match found execute a trade
            $this->trade($bid, $chosenAsk);
        }
    }

    public function ask(Ask $ask)
    {
        // add ask to collection
        $this->asks = new AskCollection($this->bids()->toArray() + [$ask]);

        // check bid collection for any matching bids
        $bids = $this->bids()->filterBySymbolAndPrice($ask->symbol(), $ask->price());

        // if match found execute trade
        if (count($bids)) {
            $chosenBid = $bids[0];

            $this->trade($chosenBid, $ask);
        }
    }

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
            $bid->seller()->shares()->filterBySymbol($bid->symbol())->toArray()
        ); // TODO: some proper error checking

        $this->transferShare($share, $bid->seller(), $ask->buyer());

        // remove bid from collection
        $this->removeBid($bid);

        // remove ask from collection
        $this->removeAsk($ask);
    }

    private function transferShare(Share $share, Seller $seller, Buyer $buyer)
    {
        // remove share from sellers share collection
        $seller->removeShare($share);

        // add share to buyers share collection
        $buyer->addShare($share);

        // update the shares owner id
        $share->transferOwnershipToBuyer($buyer);
    }

    private function removeBid(Bid $bid)
    {
        $bids = $this->bids()->toArray();
        unset($bids[$bid->id()->toString()]);

        $this->bids = new BidCollection($bids);
    }

    private function removeAsk(Ask $ask)
    {
        $asks = $this->asks()->toArray();
        unset($asks[$ask->id()->toString()]);

        $this->asks = new AskCollection($asks);
    }
}