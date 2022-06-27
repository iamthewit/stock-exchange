<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\BidAsk;

use JsonSerializable;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\BidAsk\Event\BidAdded;
use StockExchange\StockExchange\BidAsk\Event\BidRemoved;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exception\StateRestorationException;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class Bid implements DispatchableEventsInterface, JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private UuidInterface $exchangeId;
    private UuidInterface $traderId;
    private Symbol $symbol;
    private Price $price;
    /**
     * @var Event[]
     */
    private array $appliedEvents = [];
    private Event $lastAppliedEvent;

    private function __construct()
    {
    }

    /**
     * TODO: is issue a better name? are bids issued rather than created?
     *
     * @param UuidInterface $id
     * @param UuidInterface $exchangeId
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Bid
     */
    public static function create(
        UuidInterface $id,
        UuidInterface $exchangeId,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): self {
        $bid = new self();
        $bid->id = $id;
        $bid->exchangeId = $exchangeId;
        $bid->traderId = $traderId;
        $bid->symbol = $symbol;
        $bid->price = $price;

        $bidAdded = new BidAdded($bid);
        $bidAdded = $bidAdded->withMetadata($bid->eventMetaData());
        $bid->addDispatchableEvent($bidAdded);

        return $bid;
    }

    public static function restoreFromValues(
        UuidInterface $id,
        UuidInterface $exchangeId,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): Bid {
        $bid = new self();
        $bid->id = $id;
        $bid->exchangeId = $exchangeId;
        $bid->traderId = $traderId;
        $bid->symbol = $symbol;
        $bid->price = $price;

        return $bid;
    }

    public static function restoreStateFromEvents(array $events): Bid
    {
        if (empty($events)) {
            throw new StateRestorationException(
                'Can not restore state from an empty events array.'
            );
        }

        $bid = new self();

        foreach ($events as $event) {
            if (!is_a($event, Event::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that extend the Ask class.'
                );
            }

            switch ($event) {
                case is_a($event, BidAdded::class):
                    $bid->applyBidAdded($event);
                    break;

                case is_a($event, BidRemoved::class):
                    $bid->applyBidRemoved($event);
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
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
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

    public function remove(): static
    {
        // TODO: does a bid/ask need a status? a removedAt date?

        $bidRemoved = new BidRemoved(
            $this->id(),
            $this->exchangeId()
        );
        $bidRemoved = $bidRemoved->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($bidRemoved);

        return $this;
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
            'exchangeId' => $this->exchangeId()->toString(),
            'traderId' => $this->traderId()->toString(),
            'symbol' => $this->symbol()->toArray(),
            'price' => $this->price()->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

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
     * @param Event $event
     */
    private function addAppliedEvent(Event $event): void
    {
        $this->appliedEvents[] = $event;
    }

    private function applyBidAdded(BidAdded $event)
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->exchangeId = Uuid::fromString($event->payload()['exchangeId']);
        $this->traderId = Uuid::fromString($event->payload()['traderId']);
        $this->symbol = Symbol::fromValue($event->payload()['symbol']['value']);
        $this->price = Price::fromValue($event->payload()['price']['value']);

        $this->addAppliedEvent($event);
    }

    private function applyBidRemoved(BidRemoved $event)
    {
        // TODO: set a status?

        $this->addAppliedEvent($event);
    }
}
