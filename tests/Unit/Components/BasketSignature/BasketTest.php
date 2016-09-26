<?php

namespace Shopware\tests\Unit\Components\BasketSignature;

use PHPUnit\Framework\TestCase;
use Shopware\Components\BasketSignature\Basket;
use Shopware\Components\BasketSignature\BasketItem;

class BasketTest extends TestCase
{
    public function testCreateFromSBasketWithAmounts()
    {
        $this->assertEquals(
            new Basket(
                10.99,
                10.50,
                []
            ),
            Basket::createFromSBasket([
                'content' => [],
                'sAmount' => 10.99,
                'sAmountTax' => 10.50
            ])
        );
    }

    public function testCreateBasketItemFromSBasket()
    {
        $this->assertEquals(
            new BasketItem('a', 1, 19.0, 25),
            BasketItem::createFromSBasket([
                'ordernumber' => 'a',
                'quantity' => 1,
                'tax_rate' => 19.0,
                'price' => 25
            ])
        );
    }

    public function testCreateFromSBasketWithMultipleItems()
    {
        $this->assertEquals(
            new Basket(
                10.99,
                10.50,
                [
                    new BasketItem('a', 1, 19.0, 25),
                    new BasketItem('b', 1, 19.0, 25),
                    new BasketItem('c', 1, 19.0, 25),
                ]
            ),
            Basket::createFromSBasket([
                'content' => [
                    [
                        'ordernumber' => 'a',
                        'quantity' => 1,
                        'tax_rate' => 19.0,
                        'price' => 25
                    ],
                    [
                        'ordernumber' => 'b',
                        'quantity' => 1,
                        'tax_rate' => 19.0,
                        'price' => 25
                    ],
                    [
                        'ordernumber' => 'c',
                        'quantity' => 1,
                        'tax_rate' => 19.0,
                        'price' => 25
                    ]
                ],
                'sAmount' => 10.99,
                'sAmountTax' => 10.50
            ])
        );
    }
}
