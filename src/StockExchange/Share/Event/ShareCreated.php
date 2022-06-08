<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Share\Event;

use StockExchange\StockExchange\Event\Event;
use StockExchange\StockExchange\Share\Share;

class ShareCreated extends Event
{
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
