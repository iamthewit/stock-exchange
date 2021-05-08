<?php
declare(strict_types=1);


namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Bid
{
    private UuidInterface $id;
    private Seller $seller;
    private Symbol $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * TODO: is issue a better name? are bids issued rather than created?
     *
     * @param UuidInterface $id
     * @param Seller $seller
     * @param Symbol $symbol
     * @param Price $price
     * @return Bid
     */
    public static function create(
        UuidInterface $id,
        Seller $seller,
        Symbol $symbol,
        Price $price
    ): self
    {
        $bid = new self();
        $bid->id = $id;
        $bid->seller = $seller;
        $bid->symbol = $symbol;
        $bid->price = $price;

        return $bid;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Seller
     */
    public function seller(): Seller
    {
        return $this->seller;
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