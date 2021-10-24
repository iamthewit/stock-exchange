<?php

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;

class AskAddedToExchange extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Ask $ask;

    /**
     * AskAdded constructor.
     * @param Ask $ask
     */
    public function __construct(Ask $ask)
    {
        $this->init();
        $this->setPayload($ask->toArray());
        $this->ask = $ask;
    }

    /**
     * @return Ask
     */
    public function ask(): Ask
    {
        return $this->ask;
    }
}
