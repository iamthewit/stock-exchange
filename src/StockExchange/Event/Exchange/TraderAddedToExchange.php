<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Trader;

class TraderAddedToExchange extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Trader $trader;

    public function __construct(Trader $trader)
    {
        $this->init();
        $this->setPayload($trader->toArray());
        $this->trader = $trader;
    }

    public function trader(): Trader
    {
        return $this->trader;
    }
}
