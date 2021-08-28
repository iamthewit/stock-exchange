<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Event\Exchange;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\Event\EventInterface;
use StockExchange\StockExchange\Event\HasEventPayloadTrait;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Trader;

class ShareAllocatedToTrader extends DomainEvent implements EventInterface
{
    use HasEventPayloadTrait;

    private Share $share;
    private Trader $trader;

    /**
     * @param Share $share
     * @param Trader $trader
     */
    public function __construct(Share $share, Trader $trader)
    {
        $this->init();
        $this->setPayload(['share' => $share->asArray(), 'trader' => $trader->asArray()]);
        $this->share = $share;
        $this->trader = $trader;
    }

    /**
     * @return Share
     */
    public function share(): Share
    {
        return $this->share;
    }

    /**
     * @return Trader
     */
    public function trader(): Trader
    {
        return $this->trader;
    }
}
