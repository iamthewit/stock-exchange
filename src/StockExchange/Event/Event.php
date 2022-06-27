<?php

namespace StockExchange\StockExchange\Event;

use Prooph\Common\Messaging\DomainEvent;

class Event extends DomainEvent implements \JsonSerializable
{
    protected array $payload;

    public function payload(): array
    {
        return $this->payload;
    }

    public function jsonSerialize(): array
    {
        return [
            'payload' => $this->payload,
            'metadata' => $this->metadata
        ];
    }

    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}