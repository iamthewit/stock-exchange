<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface;

class Share implements JsonSerializable, ArrayableInterface
{
    private UuidInterface $id;
    private Symbol $symbol;
    // TODO: owner could be buyer/seller or the issuer (company) - needs more thought
    private ?UuidInterface $ownerId = null;

    private function __construct()
    {
    }

    /**
     * @param UuidInterface $id
     * @param Symbol $symbol
     *
     * @return Share
     */
    public static function create(UuidInterface $id, Symbol $symbol): Share
    {
        $share = new self();
        $share->id = $id;
        $share->symbol = $symbol;

        return $share;
    }

    public static function fromValues(
        UuidInterface $id,
        Symbol $symbol,
        ?UuidInterface $ownerId
    ): Share {
        $share = new self();
        $share->id = $id;
        $share->symbol = $symbol;
        $share->ownerId = $ownerId;

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
    public function transferOwnershipToTrader(Trader $trader): void
    {
        $this->ownerId = $trader->id();
    }

    /**
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id()->toString(),
            'symbol' => $this->symbol()->value(),
            'owner_id' => !is_null($this->ownerId()) ? $this->ownerId()->toString() : null,
        ];
    }

    /**
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
