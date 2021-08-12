<?php

namespace StockExchange\Infrastructure\Http\Controller;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetAllTradersQuery;
use StockExchange\Application\Query\GetTraderByIdQuery;
use StockExchange\Infrastructure\DTO\TraderCollectionDTO;
use StockExchange\Infrastructure\DTO\TraderWithoutSharesDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TraderController extends AbstractController
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

    // TODO: single action controllers
    #[Route('/trader/{id}', name: 'trader details')]
    public function resource(string $id, QueryHandlerBus $queryHandlerBus): JsonResponse
    {
        $trader = $queryHandlerBus->query(new GetTraderByIdQuery(Uuid::fromString($id)));

        return $this->json($trader);
    }
}
