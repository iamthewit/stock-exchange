<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Share implements \JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private Symbol $symbol;
    // TODO: owner could be buyer/seller or the issuer (company) - needs more thought
    private ?UuidInterface $ownerId = null;

    private function __construct()
    {
    }

    /**
     * @param Symbol $symbol
     *
     * @return static
     */
    public static function fromSymbol(Symbol $symbol): self
    {
        $share = new self();
        $share->id = Uuid::uuid4();
        $share->symbol = $symbol;

        return $share;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return Symbol
     */
    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @return UuidInterface|null
     */
    public function ownerId(): ?UuidInterface
    {
        return $this->ownerId;
    }

    /**
     * @param Trader $trader
     */
    public function transferOwnershipToTrader(Trader $trader)
    {
        // TODO:
        // dispatch an event

        $this->ownerId = $trader->id();
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id(),
            'symbol' => $this->symbol(),
            'owner_id' => $this->ownerId(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }
}
