<?php

namespace StockExchange\StockExchange\Event;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Bid;

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
        $this->setPayload($bid->asArray());
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