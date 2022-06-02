<?php

namespace StockExchange\StockExchange\Trader;

use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Trader\Event\TraderAddedToExchange;

class Trader implements DispatchableEventsInterface, \JsonSerializable, ArrayableInterface
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

    public function create(UuidInterface $id)
    {
        $trader = new self();
        $trader->id = $id;

        $traderAdded = new TraderAddedToExchange($trader);
        $traderAdded = $traderAdded->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($traderAdded);
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