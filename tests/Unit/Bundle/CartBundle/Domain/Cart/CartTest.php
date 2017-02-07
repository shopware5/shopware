<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider carts
     * @param Cart $cart
     */
    public function testCartSerializeWithDifferentCarts(Cart $cart)
    {
        static::assertNotEmpty($cart->serialize());
        static::assertEquals($cart, Cart::unserialize($cart->serialize()));
    }

    /**
     * @return array
     */
    public function carts()
    {
        return [
            [$this->getEmptyCart()],
            [$this->getCartWithProduct()],
            [$this->getCartWithProducts()],
            [$this->getCartWithProductAndNestedData()]
        ];
    }

    private function getEmptyCart()
    {
        return Cart::createExisting('test', 'test', []);
    }

    private function getCartWithProduct()
    {
        return Cart::createExisting('test', 'test', [
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 1.5, ['id' => 1])
        ]);
    }

    private function getCartWithProducts()
    {
        return Cart::createExisting('test', 'test', [
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 1.5, ['id' => 1]),
            new LineItem('B', ProductProcessor::TYPE_PRODUCT, 3.4, ['id' => 2]),
        ]);
    }

    /**
     * @return Cart
     */
    private function getCartWithProductAndNestedData()
    {
        return Cart::createExisting('test', 'test', [
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 1.5, [
                'id' => 1,
                'nested' => [
                    'name' => 'test',
                    'price' => 10,
                    'rule' => [
                        'id' => 1,
                        'tax' => 19
                    ]
                ]
            ])
        ]);
    }
}
