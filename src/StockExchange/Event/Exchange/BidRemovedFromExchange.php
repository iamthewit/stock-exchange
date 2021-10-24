<?php

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Bid;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;

class BidRemovedFromExchange extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Bid $bid;

    /**
     * RemoveBidFromExchange constructor.
     * @param Bid $bid
     */
    public function __construct(Bid $bid)
    {
        $this->init();
        $this->setPayload($bid->toArray());
        $this->bid = $bid;
    }

    /**
     * @return Bid
     */
    public function bid(): Bid
    {
        return $this->bid;
    }
}
