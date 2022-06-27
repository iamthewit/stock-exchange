<?php

namespace StockExchange\StockExchange\BidAsk\Event;

use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\Event\Event;

class AskRemoved extends Event
{
    private UuidInterface $askId;
    private UuidInterface $exchangeId;

    /**
     * AskAdded constructor.
     *
     * @param UuidInterface $askId
     */
    public function __construct(UuidInterface $askId, UuidInterface $exchangeId)
    {
        $this->init();
        $this->setPayload([
            'id' => $askId,
            'exchangeId' => $exchangeId
        ]);
        $this->ask = $askId;
    }

    /**
     * @return UuidInterface
     */
    public function askId(): UuidInterface
    {
        return $this->askId();
    }

    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }
}
