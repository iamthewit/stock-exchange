<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Ask;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;

class AskTest extends TestCase
{
    public function testToArray()
    {
        list($askId, $traderId, $ask) = $this->createAsk();

        $this->assertEquals(
            [
                'id' => $askId->toString(),
                'trader' => [
                    'id' => $traderId->toString(),
                    'shares' => []
                ],
                'symbol' => [
                    'value' => 'FOO'
                ],
                'price' => [
                    'value' => 100
                ]
            ],
            $ask->toArray()
        );
    }

    public function testJsonSerialize()
    {
        list($askId, $traderId, $ask) = $this->createAsk();

        $this->assertEquals(
            [
                'id' => $askId->toString(),
                'trader' => [
                    'id' => $traderId->toString(),
                    'shares' => []
                ],
                'symbol' => [
                    'value' => 'FOO'
                ],
                'price' => [
                    'value' => 100
                ]
            ],
            $ask->jsonSerialize()
        );
    }

    public function testJsonEncoding()
    {
        list($askId, $traderId, $ask) = $this->createAsk();

        $this->assertEquals(
            json_encode([
                'id' => $askId->toString(),
                'trader' => [
                    'id' => $traderId->toString(),
                    'shares' => []
                ],
                'symbol' => [
                    'value' => 'FOO'
                ],
                'price' => [
                    'value' => 100
                ]
            ]),
            json_encode($ask)
        );
    }

    /**
     * @return array
     * @throws ShareCollectionCreationException
     */
    private function createAsk(): array
    {
        $askId = Uuid::uuid4();
        $traderId = Uuid::uuid4();
        $ask = Ask::create(
            $askId,
            Trader::create($traderId),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        return array($askId, $traderId, $ask);
    }
}
