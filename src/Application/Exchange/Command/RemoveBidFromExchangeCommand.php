<?php


namespace StockExchange\Application\Exchange\Command;

use Ramsey\Uuid\UuidInterface;

/**
 * Class RemoveAskFromExchangeCommand
 * @package StockExchange\Application\Exchange\Command
 */
class RemoveBidFromExchangeCommand
{
    private UuidInterface $exchangeId;
    private UuidInterface $id;

    /**
     * CreateAskCommand constructor.
     *
     * @param UuidInterface $exchangeId
     * @param UuidInterface $id
     */
    public function __construct(
        UuidInterface $exchangeId,
        UuidInterface $id,
    ) {
        $this->exchangeId = $exchangeId;
        $this->id = $id;
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
    public function id(): UuidInterface
    {
        return $this->id;
    }
}