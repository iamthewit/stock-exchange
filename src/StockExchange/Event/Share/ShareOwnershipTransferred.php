<?php

namespace StockExchange\StockExchange\Event\Share;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Trader;

class ShareOwnershipTransferred extends DomainEvent implements EventInterface
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
        $this->setPayload(['trader_id' => $trader->id()->toString()]);
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
