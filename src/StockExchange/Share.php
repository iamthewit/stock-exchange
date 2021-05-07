<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Share
{
    private UuidInterface $id;
    private Symbol $symbol;
    // TODO: owner could be buyer/seller or the issuer (company) - needs more thought
    private ?UuidInterface $ownerId;

    private function __construct()
    {
    }

    public static function fromSymbol(Symbol $symbol): self
    {
        $share = new self();
        $share->id = Uuid::uuid4();
        $share->symbol = $symbol;

        return $share;
    }


    public function id()
    {
        return $this->id;
    }

    public function symbol()
    {
        return $this->symbol;
    }

    public function transferOwnershipToBuyer(Buyer $buyer)
    {
        // TODO:
        // dispatch an event

        $this->ownerId = $buyer->id();
    }
}