<?php

namespace StockExchange\StockExchange\Event;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Share;

class ShareCreatedFromSymbol extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Share $share;

    /**
     * TraderCreated constructor.
     * @param Share $share
     */
    public function __construct(Share $share)
    {
        $this->init();
        $this->setPayload($share->asArray());
        $this->share = $share;
    }

    /**
     * @return Share
     */
    public function share(): Share
    {
        return $this->share;
    }
}