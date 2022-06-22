<?php

namespace StockExchange\Infrastructure\Persistence;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;
use Ramsey\Uuid\UuidInterface;
use StockExchange\StockExchange\BidAsk\Ask;
use StockExchange\StockExchange\BidAsk\Bid;
use StockExchange\StockExchange\Exchange;
use StockExchange\StockExchange\ExchangeReadRepositoryInterface;
use StockExchange\StockExchange\Share\Event\ShareCreated;
use StockExchange\StockExchange\Share\Event\ShareOwnershipTransferred;
use StockExchange\StockExchange\Share\Share;
use StockExchange\StockExchange\Symbol;

class ExchangeMySqlEventStoreReadRepository implements ExchangeReadRepositoryInterface
{
    // TODO: rename this class ExchangeEventMySqlEventStoreReadRepository
    private ProjectionManager $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function findById(string $id): Exchange
    {
        // rebuild the state of the exchange
        $getExchangeQuery = $this->projectionManager->createQuery();
        $getExchangeQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Exchange::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Exchange::restoreStateFromEvents($getExchangeQuery->getState());
    }

    // TODO: refactor this to allow passing a class name to restore?
    public function findExchangeById(string $id): Exchange\Exchange
    {
        // rebuild the state of the exchange
        $getExchangeQuery = $this->projectionManager->createQuery();
        $getExchangeQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Exchange\Exchange::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Exchange\Exchange::restoreStateFromEvents($getExchangeQuery->getState());
    }


    public function findShareById(string $id): \StockExchange\StockExchange\Share\Share
    {
        // rebuild the state of the share
        $getShareQuery = $this->projectionManager->createQuery();
        $getShareQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Share::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Share::restoreStateFromEvents($getShareQuery->getState());
    }

    public function findShareIdsBySymbolAndTraderId(Symbol $symbol, UuidInterface $traderId): array
    {
        // TODO: this should be a projection
        $getShareQuery = $this->projectionManager->createQuery();
        $getShareQuery
            ->init(function (): array {
                return [];
            })
            ->fromCategory(Share::class)
            ->when([
                ShareCreated::class => function (array $state, Message $event) use ($symbol): array {
                    /** @var Symbol $symbol */
                    $state['shares_and_symbols'][$event->metadata()['_aggregate_id']] = $event->payload()['symbol'];

                    return $state;
                },
                ShareOwnershipTransferred::class => function (array $state, Message $event) use ($symbol): array {
                    /** @var Symbol $symbol */
                   $state['shares_and_owners'][$event->metadata()['_aggregate_id']] = $event->payload()['ownerId'];

                    return $state;
                }
            ])
            ->run()
        ;

        // filter out share that do not match the given symbol value
        $sharesAndSymbols = array_filter(
            $getShareQuery->getState()['shares_and_symbols'],
            function ($value) use ($symbol) {
                /** @var Symbol $symbol */
                return $value === $symbol->value();
            }
        );

        $sharesAndOwners = array_filter(
            $getShareQuery->getState()['shares_and_owners'],
            function($value) use ($traderId) {
                /** @var UuidInterface $traderId */
                return $value === $traderId->toString();
            }
        );

        $sharesThatMatchSymbolAndOwnerId = array_intersect_key($sharesAndOwners, $sharesAndSymbols);

        return array_keys($sharesThatMatchSymbolAndOwnerId);
    }

    public function findAskById(string $id): \StockExchange\StockExchange\BidAsk\Ask
    {
        // rebuild the state of the ask
        $getAskQuery = $this->projectionManager->createQuery();
        $getAskQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Ask::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Ask::restoreStateFromEvents($getAskQuery->getState());
    }

    public function findBidById(string $id): \StockExchange\StockExchange\BidAsk\Bid
    {
        // rebuild the state of the bid
        $getBidQuery = $this->projectionManager->createQuery();
        $getBidQuery
            ->init(function (): array {
                return [];
            })
            ->fromStream(Bid::class . '-' . $id)
            ->whenAny(function (array $state, Message $event): array {
                $state[] = $event;

                return $state;
            })
            ->run()
        ;

        // TODO: check we have some events to rebuild the exchange from
        // if not throw ExchangeNotFoundException

        return Bid::restoreStateFromEvents($getBidQuery->getState());
    }
}
