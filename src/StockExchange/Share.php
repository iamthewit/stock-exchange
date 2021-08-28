<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use JsonSerializable;
use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\Share\ShareCreatedFromSymbol;
use StockExchange\StockExchange\Event\Share\ShareOwnershipTransferred;
use StockExchange\StockExchange\Exception\StateRestorationException;

class Share implements JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private Symbol $symbol;
    // TODO: owner could be buyer/seller or the issuer (company) - needs more thought
    private ?UuidInterface $ownerId = null;
    /**
     * @var EventInterface[]
     */
    private array $appliedEvents = [];

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

        $shareCreatedFromSymbolEvent = new ShareCreatedFromSymbol($share);
        $shareCreatedFromSymbolEvent = $shareCreatedFromSymbolEvent->withMetadata($share->eventMetaData());
        $share->addDispatchableEvent($shareCreatedFromSymbolEvent);

        return $share;
    }

    /**
     * @param EventInterface[] $events
     * @return Share
     * @throws StateRestorationException
     */
    public static function restoreStateFromEvents(array $events): Share
    {
        $share = new self();

        foreach ($events as $event) {
            if (!is_a($event, EventInterface::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that implement EventInterface.'
                );
            }

            switch ($event) {
                case is_a($event, ShareCreatedFromSymbol::class):
                    $share->applyShareCreatedFromSymbol($event);
                    break;
                case is_a($event, ShareOwnershipTransferred::class):
                    $share->applyShareOwnershipTransferred($event);
                    break;
            }
        }

        return $share;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
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
     * @param Trader $trader
     */
    public function transferOwnershipToTrader(Trader $trader): void
    {
        $this->ownerId = $trader->id();

        $shareOwnershipTransferred = new ShareOwnershipTransferred($trader);
        $shareOwnershipTransferred = $shareOwnershipTransferred->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($shareOwnershipTransferred);
    }

    /**
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
    public function asArray(): array
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
        return $this->asArray();
    }

    /**
     * @param EventInterface $event
     */
    private function addAppliedEvent(EventInterface $event): void
    {
        $this->appliedEvents[] = $event;
    }

    private function applyShareCreatedFromSymbol(ShareCreatedFromSymbol $event)
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->symbol = Symbol::fromValue($event->payload()['symbol']);

        $this->addAppliedEvent($event);
    }

    private function applyShareOwnershipTransferred(EventInterface $event)
    {
        $this->ownerId = Uuid::fromString($event->payload()['trader_id']);

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
