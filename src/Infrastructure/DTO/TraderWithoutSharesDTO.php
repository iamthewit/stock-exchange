<?php

namespace StockExchange\Infrastructure\DTO;

/**
 * Class TraderWithoutSharesDTO
 * @package StockExchange\Infrastructure\DTO
 */
class TraderWithoutSharesDTO extends AbstractTraderDTO
{
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getTrader()->id()->toString()
        ];
    }
}
