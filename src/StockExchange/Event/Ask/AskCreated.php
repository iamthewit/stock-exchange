<?php

namespace StockExchange\StockExchange\Event\Ask;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Ask;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;

class AskCreated extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Ask $ask;

    /**
     * AskCreated constructor.
     * @param Ask $ask
     */
    public function __construct(Ask $ask)
    {
        $this->init();
        $this->setPayload($ask->asArray());
        $this->ask = $ask;
    }
}
