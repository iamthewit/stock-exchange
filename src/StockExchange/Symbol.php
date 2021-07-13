<?php

declare(strict_types=1);

namespace StockExchange\StockExchange;

class Symbol implements \JsonSerializable, ArrayableInterface
{
    private string $value;

    /*
     * Should the symbol just be the literal value of the symbol?
     * Or should this class include more details?
     * - Latest bid/ask price
     * - Current market value
     * - Maybe these are methods on the exchange class instead?
     * -- $exchange->latestBid(Symbol $symbol)
     * -- $exchange->latestAsk(Symbol $symbol)
     * -- $exchange->currentSymbolValue(Symbol $symbol)
     *
     */

    private function __construct()
    {
    }

    /**
     * @param string $value
     *
     * @return Symbol
     */
    public static function fromValue(string $value): self
    {
        $symbol = new self();
        $symbol->value = $value;

        return $symbol;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return array{value: string}
     */
    public function asArray(): array
    {
        return [
            'value' => $this->value()
        ];
    }

    public function jsonSerialize()
    {
        return $this->asArray();
    }
}
