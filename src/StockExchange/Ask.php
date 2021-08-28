<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Ask\AskCreated;

class Ask implements DispatchableEventsInterface, \JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

    private UuidInterface $id;
    private Trader $trader;
    private Symbol $symbol;
    private Price $price;

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     * @param Trader        $trader
     * @param Symbol        $symbol
     * @param Price         $price
     *
     * @return Ask
     */
    public static function create(
        UuidInterface $id,
        Trader $trader,
        Symbol $symbol,
        Price $price
    ): Ask {
        $ask = new self();
        $ask->id = $id;
        $ask->trader = $trader;
        $ask->symbol = $symbol;
        $ask->price = $price;

        $ask->addDispatchableEvent(new AskCreated($ask));

        return $ask;
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

    /**
     * @return array{id: string, trader: Trader, symbol: Symbol, price: Price}
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'trader' => $this->trader()->asArray(),
            'symbol' => $this->symbol()->asArray(),
            'price' => $this->price()->asArray()
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }
}
