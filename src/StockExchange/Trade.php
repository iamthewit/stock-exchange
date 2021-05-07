<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;

class Trade
{
    private UuidInterface $id;
    private Bid $bid;
    private Ask $ask;

    private function __construct()
    {
    }

    public static function fromBidAndAsk(
        UuidInterface $id,
        Bid $bid,
        Ask $ask
    )
    {
        $trade = new self();
        $trade->id = $id;
        $trade->bid = $bid;
        $trade->ask = $ask;

        return $trade;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Bid
     */
    public function bid(): Bid
    {
        return $this->bid;
    }

    /**
     * @return Ask
     */
    public function ask(): Ask
    {
        return $this->ask;
    }

}