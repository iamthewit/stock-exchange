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

    private function addDispatchableEvent(EventInterface $event): void
    {
        $this->dispatchableEvents[] = $event;
    }
}
