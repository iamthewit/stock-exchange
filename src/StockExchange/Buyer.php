<?php
declare(strict_types=1);

namespace StockExchange\StockExchange;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Buyer
{
    private UuidInterface $id;
    private ShareCollection $shares;

    private function __construct()
    {
    }

    /**
     * @return static
     * @throws Exception\ShareCollectionCreationException
     */
    public static function create(): self
    {
        $buyer = new self();
        $buyer->id = Uuid::uuid4();
        $buyer->shares = new ShareCollection([]);

        return $buyer;
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
    public function addShare(Share $share)
    {
        $this->shares = new ShareCollection($this->shares->toArray() + [$share]);
    }

}