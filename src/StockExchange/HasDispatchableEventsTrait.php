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

    private function addDispatchableEvent(EventInterface $event): void
    {
        $this->dispatchableEvents[] = $event;
    }
}
