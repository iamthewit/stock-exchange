<?php

namespace StockExchange\Infrastructure\DTO;

use Exception;
use StockExchange\StockExchange\Exception\TraderCollectionCreationException;

/**
 * Class TraderWithoutSharesCollectionDTO
 * @package StockExchange\Infrastructure\DTO
 */
class TraderCollectionDTO implements \JsonSerializable
{
    private array $traders;

    /**
     * TraderCollectionDTO constructor.
     *
     * @param array $traders
     *
     * @throws Exception
     */
    public function __construct(array $traders)
    {
        foreach ($traders as $trader) {
            if (!is_a($trader, AbstractTraderDTO::class)) {
                throw new TraderCollectionCreationException(
                    'Can only create a TraderCollectionDTO from an array of objects that each extend AbstractTraderDTO.'
                );
            }
        }

        $this->traders = $traders;
    }

    public function jsonSerialize()
    {
        return $this->traders;
    }
}
