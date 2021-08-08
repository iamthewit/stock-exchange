<?php

namespace StockExchange\Infrastructure\DTO;

use Exception;

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
                throw new Exception('You fool! What have you done!'); // TODO: sort this out
            }
        }

        $this->traders = $traders;
    }

    public function jsonSerialize()
    {
        return $this->traders;
    }
}
