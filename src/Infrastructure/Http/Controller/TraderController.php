<?php

namespace StockExchange\Infrastructure\Http\Controller;

use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetAllTradersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TraderController extends AbstractController
{
    #[Route('/trader', name: 'trader')]
    public function index(QueryHandlerBus $queryHandlerBus): Response
    {
        $query = $queryHandlerBus->query(new GetAllTradersQuery());

        return $this->json($query);
    }
}
