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

    /**
     * @param ShareCollection $shares
     *
     * @return static
     */
    public static function create(ShareCollection $shares): self
    {
        $seller = new self();
        $seller->id = Uuid::uuid4();
        $seller->shares = $shares;

        return $seller;
    }

    /**
     * @return UuidInterface
     */
    public function id(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return ShareCollection
     */
    public function shares(): ShareCollection
    {
        return $this->shares;
    }

    /**
     * @param Share $share
     *
     * @throws Exception\ShareCollectionCreationException
     */
    public function removeShare(Share $share)
    {
        $shares = $this->shares()->toArray();
        unset($shares[$share->id()->toString()]);

        $this->shares = new ShareCollection($shares);
    }
}