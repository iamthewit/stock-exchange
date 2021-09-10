<?php

namespace StockExchange\StockExchange;

use Prooph\Common\Messaging\DomainEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\Trader\TraderAddedShare;
use StockExchange\StockExchange\Event\Trader\TraderCreated;
use StockExchange\StockExchange\Event\Trader\TraderRemovedShare;
use StockExchange\StockExchange\Exception\StateRestorationException;

/**
 * Class Trader
 * @package StockExchange\StockExchange
 */
class Trader implements \JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private ShareCollection $shares;

    // we should probably store the exchange id against the trader too
    // at the moment only one exchange exists, though the exchange has an ID
    // and it is not impossible to imagine that multiple exchanges might
    // exist in the future

    /**
     * @var EventInterface[]
     */
    private array $appliedEvents = [];

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     *
     * @return self
     * @throws Exception\ShareCollectionCreationException
     */
    public static function create(UuidInterface $id): self
    {
        $trader = new self();
        $trader->id = $id;
        $trader->shares = new ShareCollection([]);

        $traderCreated = new TraderCreated($trader);
        $traderCreated = $traderCreated->withMetadata($trader->eventMetaData());
        $trader->addDispatchableEvent($traderCreated);

        return $trader;
    }

    /**
     * @param EventInterface[] $events
     * @return Trader
     * @throws StateRestorationException
     */
    public static function restoreStateFromEvents(array $events): Trader
    {
        $trader = new self();

        foreach ($events as $event) {
            if (!is_a($event, EventInterface::class)) {
                // TODO: create a proper exception for this:
                throw new StateRestorationException(
                    'Can only restore state from objects that implement EventInterface.'
                );
            }

            switch ($event) {
                case is_a($event, TraderCreated::class):
                    $trader->applyTraderCreated($event);
                    break;

                case is_a($event, TraderAddedShare::class):
                    $trader->applyTraderAddedShare($event);
                    break;

                case is_a($event, TraderRemovedShare::class):
                    $trader->applyTraderRemovedShare($event);
                    break;
            }
        }

        return $trader;
    }

    /**
     * @param UuidInterface $id
     * @param ShareCollection $shares
     * @return Trader
     */
    public static function restoreFromValues(UuidInterface $id, ShareCollection $shares): Trader
    {
        $trader = new self();
        $trader->id = $id;
        $trader->shares = $shares;

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
     * @return ShareCollection
     */
    public function shares(): ShareCollection
    {
        return $this->shares;
    }

    /**
     * @return EventInterface[]
     */
    public function appliedEvents(): array
    {
        return $this->appliedEvents;
    }

    /**
     * @param Share $share
     *
     * @throws Exception\ShareCollectionCreationException
     */
    public function addShare(Share $share): void
    {
        $this->shares = new ShareCollection($this->shares->toArray() + [$share]);

        $shareAddedToTrader = new TraderAddedShare($share);
        $shareAddedToTrader = $shareAddedToTrader->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($shareAddedToTrader);
    }

    /**
     * @param Share $share
     *
     * @throws Exception\ShareCollectionCreationException
     */
    public function removeShare(Share $share): void
    {
        $shares = $this->shares()->toArray();
        unset($shares[$share->id()->toString()]);

        $this->shares = new ShareCollection($shares);

        $traderRemovedShare = new TraderRemovedShare($share);
        $traderRemovedShare = $traderRemovedShare->withMetadata($this->eventMetaData());
        $this->addDispatchableEvent($traderRemovedShare);
    }

    /**
     * @return array<string, ShareCollection|string>
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'shares' => $this->shares()->toArray()
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
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
     * @param EventInterface $event
     */
    private function addAppliedEvent(EventInterface $event): void
    {
        $this->appliedEvents[] = $event;
    }

    private function applyTraderCreated(TraderCreated $event)
    {
        $this->id = Uuid::fromString($event->payload()['id']);
        $this->shares = new ShareCollection([]);

        $this->addAppliedEvent($event);
    }

    private function applyTraderAddedShare(TraderAddedShare $event)
    {
        $ownerId = !is_null($event->payload()['owner_id']) ? Uuid::fromString($event->payload()['owner_id']) : null;
        $share = Share::fromValues(
            Uuid::fromString($event->payload()['id']),
            Symbol::fromValue($event->payload()['symbol']),
            $ownerId
        );

        $this->shares = new ShareCollection($this->shares->toArray() + [$share]);

        $this->addAppliedEvent($event);
    }

    private function applyTraderRemovedShare(TraderRemovedShare $event)
    {
        $shares = $this->shares()->toArray();
        unset($shares[$event['id']]);
        $this->shares = new ShareCollection($shares);

        $this->addAppliedEvent($event);
    }
}
