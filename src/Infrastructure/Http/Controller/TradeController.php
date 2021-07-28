<?php

namespace StockExchange\Infrastructure\Http\Controller;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\Handler\GetExchangeByIdHandler;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetExchangeByIdQuery;
use StockExchange\Application\Query\GetTradesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TradeController extends AbstractController
{
    private QueryHandlerBus $queryHandlerBus;

    /**
     * TradeController constructor.
     *
     * @param QueryHandlerBus $queryHandlerBus
     */
    public function __construct(QueryHandlerBus $queryHandlerBus)
    {
        $this->queryHandlerBus = $queryHandlerBus;
    }

    #[Route('/trade', name: 'trade')]
    public function index(): Response
    {
        // get all trades
        $exchangeId = Uuid::fromString($this->getParameter('stock_exchange.default_exchange_id'));
        $exchange = $this->queryHandlerBus->query(new GetExchangeByIdQuery($exchangeId));
        $trades = $this->queryHandlerBus->query(new GetTradesQuery($exchange));

        \Kint::dump($trades);die;

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Infrastructure/Http/Controller/TradeController.php',
        ]);
    }
}
