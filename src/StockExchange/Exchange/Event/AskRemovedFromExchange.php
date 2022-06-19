<?php

namespace StockExchange\StockExchange\Exchange\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Event;

class AskRemovedFromExchange extends Event
{
    private UuidInterface $askId;

    /**
     * RemoveAskFromExchange constructor.
     *
     * @param UuidInterface $askId
     */
    public function __construct(UuidInterface $askId)
    {
        $this->init();
        $this->setPayload(['askId' => $askId]);
        $this->askId = $askId;
    }

    /**
     * @return UuidInterface
     */
    public function askId(): UuidInterface
    {
        return $this->askId;
    }
}
