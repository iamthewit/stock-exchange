<?php

namespace StockExchange\Tests\StockExchange;

use Ramsey\Uuid\Uuid;
use StockExchange\StockExchange\Trader;
use StockExchange\StockExchange\Share;
use PHPUnit\Framework\TestCase;
use StockExchange\StockExchange\Symbol;

class ShareTest extends TestCase
{

    public function testItTransfersOwnershipToBuyer()
    {
        $buyer = Trader::create(Uuid::uuid4());
        $share = Share::create(Uuid::uuid4(), Symbol::fromValue('FOO'));

        $share->transferOwnershipToTrader($buyer);

        $this->assertEquals($buyer->id()->toString(), $share->ownerId()->toString());
    }

    /**
     * @dataProvider shareProvider
     */
    public function testToArray($share, array $expected)
    {
        $this->assertEquals($expected, $share->toArray());
    }

    /**
     * @dataProvider shareProvider
     */
    public function testJsonSerialize(Share $share, array $expected)
    {
        $this->assertEquals($expected, $share->jsonSerialize());
    }

    /**
     * @dataProvider shareProvider
     */
    public function testJsonEncodePrice(Share $share, array $expected)
    {
        $this->assertEquals(
            json_encode($expected),
            json_encode($share)
        );
    }

    private function shareProvider(): array
    {
        $shareId = Uuid::uuid4();
        $shareWithoutTrader = Share::create($shareId, Symbol::fromValue('FOO'));

        $traderId = Uuid::uuid4();
        $trader = Trader::create($traderId);
        $shareWithTrader = Share::create($shareId, Symbol::fromValue('FOO'));
        $shareWithTrader->transferOwnershipToTrader($trader);
        return [
            'share_without_trader' => [
                $shareWithoutTrader,
                [
                    'id' => $shareId->toString(),
                    'symbol' => 'FOO',
                    'owner_id' => null
                ]
            ],
            'share_with_trader' => [
                $shareWithTrader,
                [
                    'id' => $shareId->toString(),
                    'symbol' => 'FOO',
                    'owner_id' => $traderId->toString()
                ]
            ]
        ];
    }
}
