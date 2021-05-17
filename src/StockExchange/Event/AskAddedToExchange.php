<?php

namespace StockExchange\StockExchange\Event;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Ask;

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
        $this->setPayload($ask->asArray());
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