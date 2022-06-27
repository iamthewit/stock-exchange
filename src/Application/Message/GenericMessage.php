<?php


namespace StockExchange\Application\Message;

/**
 * Class GenericMessage
 * @package ExchangeReport\Application\Message
 */
class GenericMessage
{
    private string $type;
    private array  $data;

    /**
     * GenericMessage constructor.
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct(string $type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function data(): array
    {
        return $this->data;
    }
}