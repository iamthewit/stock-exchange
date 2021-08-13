<?php

namespace StockExchange\Infrastructure\Http\Controller\Trader;

use Ramsey\Uuid\Uuid;
use StockExchange\Application\MessageBus\QueryHandlerBus;
use StockExchange\Application\Query\GetTraderByIdQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GetTraderController extends AbstractController
{
    #[Route('/trader/{id}', name: 'trader details')]
    public function resource(string $id, QueryHandlerBus $queryHandlerBus): JsonResponse
    {
        $trader = $queryHandlerBus->query(new GetTraderByIdQuery(Uuid::fromString($id)));

        return $this->json($trader);
    }
}
