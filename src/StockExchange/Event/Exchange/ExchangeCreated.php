<?php

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Exchange;

class ExchangeCreated extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Exchange $exchange;

    /**
     * ExchangeCreated constructor.
     * @param Exchange $exchange
     */
    public function __construct(Exchange $exchange)
    {
        $this->init();
        $this->setPayload($exchange->asArray());
        $this->exchange = $exchange;
    }

    /**
     * @return Exchange
     */
    public function exchange(): Exchange
    {
        return $this->exchange;
    }
}