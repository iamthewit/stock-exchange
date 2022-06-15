<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Share;

use JsonSerializable;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\ArrayableInterface;
use StockExchange\StockExchange\DispatchableEventsInterface;
use StockExchange\StockExchange\HasDispatchableEventsTrait;
use StockExchange\StockExchange\Share\Event\ShareCreated;
use StockExchange\StockExchange\Symbol;

class Share implements DispatchableEventsInterface, JsonSerializable, ArrayableInterface
{
    use HasDispatchableEventsTrait;

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

        $shareCreated = new ShareCreated($share);
        $shareCreated = $shareCreated->withMetadata($share->eventMetaData());
        $share->addDispatchableEvent($shareCreated);

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
     * @param UuidInterface $traderId
     */
    public function transferOwnershipToTrader(UuidInterface $traderId): void
    {
        $this->ownerId = $traderId;
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
