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
     * @return Share
     */
    public static function fromSymbol(Symbol $symbol): Share
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
    public function transferOwnershipToTrader(Trader $trader): void
    {
        // TODO:
        // dispatch an event

        $this->ownerId = $trader->id();
    }

    /**
     * @return array{id: string, symbol: string, owner_id: string|null}
     */
    public function asArray(): array
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
        return $this->asArray();
    }
}
