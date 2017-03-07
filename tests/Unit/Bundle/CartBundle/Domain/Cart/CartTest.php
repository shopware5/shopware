<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Cart;

use Shopware\Bundle\CartBundle\Domain\Cart\Cart;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider carts
     *
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
            [$this->getCartWithProductAndNestedData()],
        ];
    }

    private function getEmptyCart()
    {
        return Cart::createExisting('test', 'test', []);
    }

    private function getCartWithProduct()
    {
        return Cart::createExisting('test', 'test', [
            new LineItem('A', ProductProcessor::TYPE_PRODUCT, 1.5, ['id' => 1]),
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
                        'tax' => 19,
                    ],
                ],
            ]),
        ]);
    }
}
