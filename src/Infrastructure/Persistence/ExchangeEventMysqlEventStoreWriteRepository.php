<?php

namespace StockExchange\Infrastructure\Persistence;

use ArrayIterator;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use StockExchange\StockExchange\ExchangeEventWriteRepositoryInterface;

class ExchangeEventMysqlEventStoreWriteRepository implements ExchangeEventWriteRepositoryInterface
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function storeEvent(DomainEvent $event): void
    {
        // store the command in our event store
        $aggregate = $event->metadata()['_aggregate_type'];
        $aggregateId = $event->metadata()['_aggregate_id'];

        $streamName = new StreamName($aggregate . '-' . $aggregateId);

        // TODO: PHP 8.1 has broken the comparison made by the prooph library when
        // checking hasStream(), so I've added a hacky work around for now...
        $streamExists = false;
        foreach ($this->eventStore->fetchStreamNames(null, null) as $fetchedStreamName) {
            if ($fetchedStreamName->toString() === $streamName->toString()) {
                $streamExists = true;
                break;
            }
        }

//        if (!$this->eventStore->hasStream($streamName)) {
        if (!$streamExists) {
            $stream = new Stream(
                $streamName,
                new ArrayIterator(),
                []
            );

            $this->eventStore->create($stream);
        }

        $this->eventStore->appendTo(
            $streamName,
            new ArrayIterator([$event])
        );
    }
}