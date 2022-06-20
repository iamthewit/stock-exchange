<?php

declare(strict_types=1);

namespace StockExchange\StockExchange\Share\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Event;

class ShareOwnershipTransferred extends Event
{
    private UuidInterface $shareId;

    /**
     * @param UuidInterface $shareId
     */
    public function __construct(UuidInterface $shareId)
    {
        $this->init();
        $this->setPayload(['ownerId' => $shareId]);
        $this->shareId = $shareId;
    }

    /**
     * @return UuidInterface
     */
    public function shareId(): UuidInterface
    {
        return $this->shareId;
    }
}
