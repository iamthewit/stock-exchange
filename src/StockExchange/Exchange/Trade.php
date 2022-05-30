<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Exchange;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;

class Trade implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private Bid $bid; // TODO: this will be a Bid in the Exchange context
    private Ask $ask; // TODO: this will be an Ask in the Exchange context

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     * @param Bid           $bid
     * @param Ask           $ask
     *
     * @return Trade
     */
    public static function fromBidAndAsk(
        UuidInterface $id,
        Bid $bid,
        Ask $ask
    ): self {
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

    /**
     * @return array{id: string, bid: Bid, ask: Ask}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'bid' => $this->bid()->toArray(),
            'ask' => $this->ask()->toArray()
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
