<?php

namespace StockExchange\Application\Command;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;

class CreateAskCommand
{
    private Exchange $exchange;
    private UuidInterface $id;
    private Trader $trader;
    private Symbol $symbol;
    private Price $price;

    /**
     * CreateAskCommand constructor.
     * @param Exchange $exchange
     * @param UuidInterface $id
     * @param Trader $trader
     * @param Symbol $symbol
     * @param Price $price
     */
    public function __construct(Exchange $exchange, UuidInterface $id, Trader $trader, Symbol $symbol, Price $price)
    {
        $this->exchange = $exchange;
        $this->id = $id;
        $this->trader = $trader;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    /**
     * @return Exchange
     */
    public function exchange(): Exchange
    {
        return $this->exchange;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Trader
     */
    public function trader(): Trader
    {
        return $this->trader;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return Price
     */
    public function price(): Price
    {
        return $this->price;
    }

}