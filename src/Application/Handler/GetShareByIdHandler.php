<?php

namespace StockExchange\Application\Handler;

use StockExchange\Application\Query\GetShareByIdQuery;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Share;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GetShareByIdHandler implements MessageHandlerInterface
{
    private ExchangeReadRepositoryInterface $exchangeReadRepository;

    /**
     * @param ExchangeReadRepositoryInterface $exchangeReadRepository
     */
    public function __construct(ExchangeReadRepositoryInterface $exchangeReadRepository)
    {
        $this->exchangeReadRepository = $exchangeReadRepository;
    }

    public function __invoke(GetShareByIdQuery $query): Share
    {
        $exchange = $this->exchangeReadRepository->findById($query->exchangeId()->toString());

        return $exchange->shares()->findById($query->id());
    }
}
