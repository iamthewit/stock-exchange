<?php

namespace StockExchange\StockExchange\Event\Trader;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Share;

class TraderAddedShare extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    /**
     * @var Share
     */
    private Share $share;

    /**
     * ShareAddedToTrader constructor.
     * @param Share $share
     */
    public function __construct(Share $share)
    {
        $this->init();
        $this->setPayload($share->asArray());

        $this->share = $share;
    }
}
