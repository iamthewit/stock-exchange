<?php

namespace StockExchange\StockExchange\Event\Trader;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Trader;

class TraderCreated extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Trader $trader;

    /**
     * TraderCreated constructor.
     * @param Trader $trader
     */
    public function __construct(Trader $trader)
    {
        $this->init();
        $this->setPayload($trader->asArray());
        $this->trader = $trader;
    }

    /**
     * @return Trader
     */
    public function trader(): Trader
    {
        return $this->trader;
    }
}
