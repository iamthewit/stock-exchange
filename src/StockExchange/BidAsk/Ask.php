<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\BidAsk;

use JsonSerializable;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\BidAsk\Event\AskAdded;
use StockExchange\StockExchange\BidAsk\Event\AskRemoved;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Exception\StateRestorationException;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;

class Ask implements DispatchableEventsInterface, JsonSerializable, ArrayableInterface
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
     * @param UuidInterface $id
     * @param UuidInterface $exchangeId
     * @param UuidInterface $traderId
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Ask
     */
    public static function create(
        UuidInterface $id,
        UuidInterface $exchangeId,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): Ask {
        $ask = new self();
        $ask->id = $id;
        $ask->exchangeId = $exchangeId;
        $ask->traderId = $traderId;
        $ask->symbol = $symbol;
        $ask->price = $price;

        $askAdded = new AskAdded($ask);
        $askAdded = $askAdded->withMetadata($ask->eventMetaData());
        $ask->addDispatchableEvent($askAdded);

        return $ask;
    }

    public static function restoreFromValues(
        UuidInterface $id,
        UuidInterface $exchangeId,
        UuidInterface $traderId,
        Symbol $symbol,
        Price $price
    ): Ask {
        $ask = new self();
        $ask->id = $id;
        $ask->exchangeId = $exchangeId;
        $ask->traderId = $traderId;
        $ask->symbol = $symbol;
        $ask->price = $price;

        return $ask;
    }

    public static function restoreStateFromEvents(array $events): Ask
    {
        $ask = new self();

        foreach ($events as $event) {
            if (!is_a($event, Event::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that extend the Ask class.'
                );
            }

            switch ($event) {
                case is_a($event, AskAdded::class):
                    $ask->applyAskAdded($event);
                    break;

                case is_a($event, AskRemoved::class):
                    $ask->applyaskRemoved($event);
                    break;
            }
        }

        return $ask;
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

        $askRemoved = new AskRemoved($this->id());
        $askRemoved = $askRemoved->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($askRemoved);

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
            'price' => $this->price()->toArray()
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

    private function applyAskAdded(AskAdded $event)
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->exchangeId = Uuid::fromString($event->payload()['exchangeId']);
        $this->traderId = Uuid::fromString($event->payload()['traderId']);
        $this->symbol = Symbol::fromValue($event->payload()['symbol']['value']);
        $this->price = Price::fromValue($event->payload()['price']['value']);

        $this->addAppliedEvent($event);
    }

    private function applyAskRemoved(AskRemoved $event)
    {
        // TODO: set a status?

        $this->addAppliedEvent($event);
    }
}
