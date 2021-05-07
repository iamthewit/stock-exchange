<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Seller
{
    private UuidInterface $id;
    private ShareCollection $shares;

    private function __construct()
    {
    }

    public static function create(ShareCollection $shares): self
    {
        $seller = new self();
        $seller->id = Uuid::uuid4();
        $seller->shares = $shares;

        return $seller;
    }

    public function id()
    {
        return $this->id;
    }

    public function shares(): ShareCollection
    {
        return $this->shares;
    }

    public function removeShare(Share $share)
    {
        $shares = $this->shares()->toArray();
        unset($shares[$share->id()->toString()]);

        $this->shares = new ShareCollection($shares);
    }
}