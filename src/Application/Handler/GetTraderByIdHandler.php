<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetTraderByIdQuery;
use StockExchange\StockExchange\Exception\ExchangeNotFoundException;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Trader;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetTraderByIdHandler implements MessageHandlerInterface
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
     * @throws ExchangeNotFoundException
     */
    public function __invoke(GetTraderByIdQuery $query): Trader
    {
        $exchange = $this->exchangeReadRepository->findById($query->exchangeId()->toString());

        return $exchange->traders()->findById($query->id());
    }
}
