<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Bid;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Exception\ShareCollectionCreationException;
use StockExchange\StockExchange\Price;
use StockExchange\StockExchange\Symbol;
use StockExchange\StockExchange\Trader;

class BidTest extends TestCase
{
    public function testToArray()
    {
        list($bidId, $traderId, $bid) = $this->createBid();

        $this->assertEquals(
            [
                'id' => $bidId->toString(),
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
            $bid->toArray()
        );
    }

    public function testJsonSerialize()
    {
        list($bidId, $traderId, $bid) = $this->createBid();

        $this->assertEquals(
            [
                'id' => $bidId->toString(),
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
            $bid->jsonSerialize()
        );
    }

    public function testJsonEncoding()
    {
        list($bidId, $traderId, $bid) = $this->createBid();

        $this->assertEquals(
            json_encode([
                'id' => $bidId->toString(),
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
            json_encode($bid)
        );
    }

    /**
     * @return array
     * @throws ShareCollectionCreationException
     */
    private function createBid(): array
    {
        $bidId = Uuid::uuid4();
        $traderId = Uuid::uuid4();
        $bid = Bid::create(
            $bidId,
            Trader::create($traderId),
            Symbol::fromValue('FOO'),
            Price::fromValue(100)
        );

        return array($bidId, $traderId, $bid);
    }
}
