<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetTradesQuery;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\TradeCollection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetTradesHandler implements MessageHandlerInterface
{
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    /**
     * @param ExchangeReadRepositoryInterface $exchangeReadRepository
     */
    public function __construct(ExchangeReadRepositoryInterface $exchangeReadRepository)
    {
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(GetTradesQuery $query): TradeCollection
    {
        $exchange = $this->exchangeReadRepository->findById($query->exchangeId()->toString());

        return $exchange->trades();
    }
}
