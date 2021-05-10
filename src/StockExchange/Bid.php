<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\BidCreated;
use StockExchange\StockExchange\Event\EventInterface;

class Bid
{
    private UuidInterface $id;
    private Trader $trader;
    private Symbol $symbol;
    private Price $price;
    private array $dispatchableEvents = [];

    private function __construct()
    {
    }

    /**
     * TODO: is issue a better name? are bids issued rather than created?
     *
     * @param UuidInterface $id
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Bid
     */
    public static function create(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): self
    {
        $bid = new self();
        $bid->id = $id;
        $bid->trader = $trader;
        $bid->symbol = $symbol;
        $bid->price = $price;

        $bid->addDispatchableEvent(new BidCreated($bid));

        return $bid;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Trader
     */
    public function trader(): Trader
    {
        return $this->trader;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return Price
     */
    public function price(): Price
    {
        return $this->price;
    }

    public function dispatchableEvents(): array
    {
        return $this->dispatchableEvents;
    }

    private function addDispatchableEvent(EventInterface $event)
    {
        $this->dispatchableEvents[] = $event;
    }
}