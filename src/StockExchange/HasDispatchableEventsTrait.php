<?php


namespace StockExchange\StockExchange;


use StockExchange\StockExchange\Event\EventInterface;

trait HasDispatchableEventsTrait
{
    private array $dispatchableEvents = [];

    public function dispatchableEvents(): array
    {
        return $this->dispatchableEvents;
    }

    private function addDispatchableEvent(EventInterface $event)
    {
        $this->dispatchableEvents[] = $event;
    }
}