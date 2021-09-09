<?php

namespace StockExchange\StockExchange;

use StockExchange\StockExchange\Event\EventInterface;

trait HasDispatchableEventsTrait
{
    /**
     * @var EventInterface[]
     */
    private array $dispatchableEvents = [];

    /**
     * @return EventInterface[]
     */
    public function dispatchableEvents(): array
    {
        return $this->dispatchableEvents;
    }

    public function clearDispatchableEvents()
    {
        // TODO: this is dirty
        // what we really want is an array of dispatched events
        // Create a custom message bus that dispatches domain events, adds
        // the events to the dispatchedEvents property and then clears the
        // dispatchableEvents property
        $this->dispatchableEvents = [];
    }

    // TODO: this feels like a hack, but will work until I can think of something better...
    // essentially this method is used when restoring state and call the 'apply' methods
    // the apply methods effectively perform the same operations as the operations as the
    // initial state mutation methods (i.e $entity->create() and $entity->applyCreated())
    // this is because we want all of our entities within the aggregate to be in the correct
    // state after restoration - the easiest way to do this is to replay the logic but not
    // dispatch the events
    public function applyDispatchableEvents()
    {
        foreach ($this->dispatchableEvents() as $event) {
            $this->addAppliedEvent($event);
        }
        $this->clearDispatchableEvents();
    }

    private function addDispatchableEvent(EventInterface $event): void
    {
        $this->dispatchableEvents[] = $event;
    }
}
