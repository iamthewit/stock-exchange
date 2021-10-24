<?php

namespace StockExchange\Application\Listener;

use Prooph\Common\Messaging\DomainEvent;
use StockExchange\StockExchange\ExchangeEventWriteRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StockExchangeEventListener implements MessageHandlerInterface
{
    private ExchangeEventWriteRepositoryInterface $exchangeEventWriteRepository;

    public function __construct(ExchangeEventWriteRepositoryInterface $exchangeEventWriteRepository)
    {
        $this->exchangeEventWriteRepository = $exchangeEventWriteRepository;
    }

    public function __invoke(DomainEvent $event): void
    {
        $this->exchangeEventWriteRepository->storeEvent($event);
    }
}
