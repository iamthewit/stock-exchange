<?php

namespace StockExchange\StockExchange\Event;

trait HasEventPayloadTrait
{
    protected array $payload;

    public function payload(): array
    {
        return $this->payload;
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}