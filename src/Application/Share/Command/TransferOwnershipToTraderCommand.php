<?php


namespace StockExchange\Application\Share\Command;


use Ramsey\Uuid\UuidInterface;

class TransferOwnershipToTraderCommand
{
    private UuidInterface $exchangeId;
    private UuidInterface $shareId;
    private UuidInterface $traderId;

    /**
     * AllocateShareToTraderCommand constructor.
     *
     * @param UuidInterface $exchangeId
     * @param UuidInterface $shareId
     * @param UuidInterface $traderId
     */
    public function __construct(UuidInterface $exchangeId, UuidInterface $shareId, UuidInterface $traderId)
    {
        $this->exchangeId = $exchangeId;
        $this->shareId  = $shareId;
        $this->traderId = $traderId;
    }

    /**
     * @return UuidInterface
     */
    public function exchangeId(): UuidInterface
    {
        return $this->exchangeId;
    }

    /**
     * @return UuidInterface
     */
    public function shareId(): UuidInterface
    {
        return $this->shareId;
    }

    /**
     * @return UuidInterface
     */
    public function traderId(): UuidInterface
    {
        return $this->traderId;
    }
}