<?php


namespace StockExchange\Application\Message;

/**
 * Class GenericMessage
 * @package ExchangeReport\Application\Message
 */
class GenericMessage
{
    private string $type;
    private array $payload;

    /**
     * GenericMessage constructor.
     *
     * @param string $type
     * @param array  $payload
     */
    public function __construct(string $type, array $payload)
    {
        $this->type    = $type;
        $this->payload = $payload;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}