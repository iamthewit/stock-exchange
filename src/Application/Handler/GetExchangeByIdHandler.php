<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\StockExchange\Exception\ExchangeNotFoundException;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetExchangeByIdHandler implements MessageHandlerInterface
{
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    /**
     * @param ExchangeReadRepositoryInterface $exchangeReadRepository
     */
    public function __construct(ExchangeReadRepositoryInterface $exchangeReadRepository)
    {
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    /**
     * @param GetExchangeByIdQuery $query
     *
     * @return Exchange
     * @throws ExchangeNotFoundException
     */
    public function __invoke(GetExchangeByIdQuery $query): Exchange
    {
        return $this->exchangeReadRepository->findById($query->id()->toString());
    }
}
