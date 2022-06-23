<?php

namespace StockExchange\StockExchange\Trader;

use JsonSerializable;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Trader\Event\TraderCreated;

class Trader implements DispatchableEventsInterface, JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    /**
     * @var Event[]
     */
    private array $appliedEvents = [];
    private Event $lastAppliedEvent;

    private function __construct()
    {
    }

    public static function create(UuidInterface $id): Trader
    {
        $trader = new self();
        $trader->id = $id;

        // TODO: add $exchangeId - can a single trader be a member of multiple exchanges ???

        $traderAdded = new TraderCreated($trader);
        $traderAdded = $traderAdded->withMetadata($trader->eventMetaData());
        $trader->addDispatchableEvent($traderAdded);

        return $trader;
    }

    /**
     * @param UuidInterface $id
     * @return Trader
     */
    public static function restoreFromValues(UuidInterface $id): Trader
    {
        $trader = new self();
        $trader->id = $id;

        return $trader;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
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
}