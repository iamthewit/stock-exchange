<?php

namespace StockExchange\Infrastructure\Http\Controller\Trader;

use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetAllTradersQuery;
use StockExchange\Infrastructure\DTO\TraderCollectionDTO;
use StockExchange\Infrastructure\DTO\TraderWithoutSharesDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListTradersController extends AbstractController
{
    #[Route('/trader', name: 'trader')]
    public function index(QueryHandlerBus $queryHandlerBus): Response
    {
        $traders = $queryHandlerBus->query(new GetAllTradersQuery());

        $traderDTOs = [];
        foreach ($traders as $trader) {
            $traderDTOs[] = new TraderWithoutSharesDTO($trader);
        }

        return $this->json(new TraderCollectionDTO($traderDTOs));
    }
}
