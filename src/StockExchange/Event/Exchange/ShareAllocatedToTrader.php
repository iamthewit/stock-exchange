<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Event\Exchange;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Trader;

class ShareAllocatedToTrader extends Event
{
    private Share $share;
    private Trader $trader;

    /**
     * @param Share $share
     * @param Trader $trader
     */
    public function __construct(Share $share, Trader $trader)
    {
        $this->init();
        $this->setPayload(['share' => $share->toArray(), 'trader' => $trader->toArray()]);
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
