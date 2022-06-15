<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Share;

use JsonSerializable;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Share\Event\ShareCreated;
use StockExchange\StockExchange\Symbol;

class Share implements DispatchableEventsInterface, JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private Symbol $symbol;
    // TODO: owner could be buyer/seller or the issuer (company) - needs more thought
    private ?UuidInterface $ownerId = null;

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
     * @param Symbol $symbol
     *
     * @return Share
     */
    public static function create(UuidInterface $id, Symbol $symbol): Share
    {
        $share = new self();
        $share->id = $id;
        $share->symbol = $symbol;

        $shareCreated = new ShareCreated($share);
        $shareCreated = $shareCreated->withMetadata($share->eventMetaData());
        $share->addDispatchableEvent($shareCreated);

        return $share;
    }

    public static function fromValues(
        UuidInterface $id,
        Symbol $symbol,
        ?UuidInterface $ownerId
    ): Share {
        $share = new self();
        $share->id = $id;
        $share->symbol = $symbol;
        $share->ownerId = $ownerId;

        return $share;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return UuidInterface|null
     */
    public function ownerId(): ?UuidInterface
    {
        return $this->ownerId;
    }

    /**
     * @param UuidInterface $traderId
     */
    public function transferOwnershipToTrader(UuidInterface $traderId): void
    {
        $this->ownerId = $traderId;
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
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'symbol' => $this->symbol()->value(),
            'owner_id' => !is_null($this->ownerId()) ? $this->ownerId()->toString() : null,
        ];
    }

    /**
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
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
