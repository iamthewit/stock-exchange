<?php

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Trade;

class TradeExecuted extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Trade $trade;

    /**
     * TradeExecuted constructor.
     * @param Trade $trade
     */
    public function __construct(Trade $trade)
    {
        $this->init();
        $this->setPayload($trade->toArray());
        $this->trade = $trade;
    }

    /**
     * @return Trade
     */
    public function trade(): Trade
    {
        return $this->trade;
    }
}
