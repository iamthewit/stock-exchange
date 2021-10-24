<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Share;

class ShareAddedToExchange extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Share $share;

    /**
     * @param Share $share
     */
    public function __construct(Share $share)
    {
        $this->init();
        $this->setPayload($share->toArray());
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
