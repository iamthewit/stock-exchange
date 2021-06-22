<?php

namespace StockExchange\Application\Listener;

use ArrayIterator;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class StockExchangeEventListener implements MessageHandlerInterface
{
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function __invoke(DomainEvent $event)
    {
        // store the command in our event store
        $aggregate = $event->metadata()['_aggregate_type'];
        $aggregateId = $event->metadata()['_aggregate_id'];

        $streamName = new StreamName($aggregate . '-' . $aggregateId);

        if (!$this->eventStore->hasStream($streamName)) {
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
