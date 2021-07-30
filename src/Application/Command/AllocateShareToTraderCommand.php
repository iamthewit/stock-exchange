<?php

namespace StockExchange\Application\Command;

use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Share;
use StockExchange\StockExchange\Trader;

/**
 * Class AllocateShareToTraderCommand
 * @package StockExchange\Application\Command
 */
class AllocateShareToTraderCommand
{
    private Exchange $exchange;
    private Share $share;
    private Trader $trader;

    /**
     * AllocateShareToTraderCommand constructor.
     *
     * @param Exchange $exchange
     * @param Share    $share
     * @param Trader   $trader
     */
    public function __construct(Exchange $exchange, Share $share, Trader $trader)
    {
        $this->exchange = $exchange;
        $this->share  = $share;
        $this->trader = $trader;
    }

    public function exchange(): Exchange
    {
        return $this->exchange;
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
