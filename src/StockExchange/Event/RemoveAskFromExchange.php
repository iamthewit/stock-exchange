<?php


namespace StockExchange\StockExchange\Event;


use StockExchange\StockExchange\Ask;

class RemoveAskFromExchange implements EventInterface
{
    private Ask $ask;

    /**
     * RemoveAskFromExchange constructor.
     * @param Ask $ask
     */
    public function __construct(Ask $ask)
    {
        $this->ask = $ask;
    }

    /**
     * @return Ask
     */
    public function ask(): Ask
    {
        return $this->ask;
    }
}