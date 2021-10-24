<?php

namespace StockExchange\StockExchange;

use Prooph\Common\Messaging\DomainEvent;

/**
 * It doesn't really make sense for this interface to belong in the Domain layer.
 * Nothing from the domain layer is ever going to persist an Exchange. It should
 * be in the Application layer instead.
 *
 * The same could be said for the Read Repository. As far as this bounded context
 * is concerned the domain has no need to access and read data from the repository.
 */
interface ExchangeEventWriteRepositoryInterface
{
    public function storeEvent(DomainEvent $event): void;
}