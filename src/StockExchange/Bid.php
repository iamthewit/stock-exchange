<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\BidCreated;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Exception\StateRestorationException;

class Bid implements DispatchableEventsInterface, \JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private Trader $trader;
    private Symbol $symbol;
    private Price $price;
    /**
     * @var EventInterface[]
     */
    private array $appliedEvents = [];

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
    ): self {
        $bid = new self();
        $bid->id = $id;
        $bid->trader = $trader;
        $bid->symbol = $symbol;
        $bid->price = $price;

        $bidCreated = new BidCreated($bid);
        $bidCreated = $bidCreated->withMetadata($bid->eventMetaData());
        $bid->addDispatchableEvent($bidCreated);

        return $bid;
    }

    /**
     * @param array $events
     *
     * @return Bid
     * @throws StateRestorationException
     */
    public static function restoreStateFromEvents(array $events): Bid
    {
        $bid = new self();

        foreach ($events as $event) {
            if (!is_a($event, EventInterface::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that implement EventInterface.'
                );
            }

            switch ($event) {
                case is_a($event, BidCreated::class):
                    $bid->applyBidCreated($event);
                    break;
            }
        }

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

    /**
     * @return EventInterface[]
     */
    public function appliedEvents(): array
    {
        return $this->appliedEvents;
    }

    /**
     * @param EventInterface $event
     */
    private function addAppliedEvent(EventInterface $event): void
    {
        $this->appliedEvents[] = $event;
    }

    /**
     * @return array{id: string, trader: Trader, symbol: Symbol, price: Price}
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'trader' => $this->trader(),
            'symbol' => $this->symbol(),
            'price' => $this->price(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
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

    // TODO: test this:
    private function applyBidCreated(BidCreated $event)
    {
        $this->id = Uuid::fromString($event->payload()['id']);
//        $this->trader = $event->payload()['trader'];
        $this->symbol = Symbol::fromValue($event->payload()['symbol']);
        $this->price = Price::fromValue($event->payload()['price']);

        $this->addAppliedEvent($event);
    }
}
