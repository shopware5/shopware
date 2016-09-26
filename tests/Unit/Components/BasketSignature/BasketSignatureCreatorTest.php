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

namespace Shopware\tests\Unit\Components\BasketSignature;

use PHPUnit\Framework\TestCase;
use Shopware\Components\BasketSignature\Basket;
use Shopware\Components\BasketSignature\BasketItem;
use Shopware\Components\BasketSignature\BasketSignatureGenerator;

class BasketSignatureGeneratorTest extends TestCase
{
    public function testSignatureCanBeCreatedForEmptyBasket()
    {
        $signatureCreator = new BasketSignatureGenerator();
        $signature = $signatureCreator->generateSignature(new Basket(0.0, 0.0, []), null);

        $this->assertNotEmpty($signature);
    }

    public function testSignatureConsidersItemTaxRate()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, null, 19.0, null)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, null, 20.0, null)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersItemQuantity()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, 1, null, null)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, 2, null, null)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersItemPrice()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, null, null, 19.99)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem(null, null, null, 20.00)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersItemNumber()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem('a', null, null, null)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    0.0,
                    0.0,
                    [new BasketItem('b', null, null, null)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersBasketAmount()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    0.0,
                    [new BasketItem('a', null, null, null)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    11.0,
                    0.0,
                    [new BasketItem('a', null, null, null)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersBasketTaxAmount()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    19.0,
                    [new BasketItem('a', null, null, null)]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    20.0,
                    [new BasketItem('a', null, null, null)]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersMultipleItems()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    0.0,
                    [
                        new BasketItem('a', null, null, null),
                        new BasketItem('a', null, null, null),
                        new BasketItem('a', null, null, null)
                    ]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    0.0,
                    [
                        new BasketItem('a', null, null, null),
                        new BasketItem('a', null, null, null),
                        new BasketItem('a', null, null, null),
                        new BasketItem('a', null, null, null)
                    ]
                ),
                null
            )
        );
    }

    public function testSignatureDoesNotConsidersItemOrder()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertSame(
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    0.0,
                    [
                        new BasketItem('a', null, null, 1),
                        new BasketItem('a', null, null, 2),
                        new BasketItem('a', null, null, 3)
                    ]
                ),
                null
            ),
            $signatureCreator->generateSignature(
                new Basket(
                    10.0,
                    0.0,
                    [
                        new BasketItem('a', null, null, 3),
                        new BasketItem('a', null, null, 1),
                        new BasketItem('a', null, null, 2)
                    ]
                ),
                null
            )
        );
    }

    public function testSignatureConsidersCustomerId()
    {
        $signatureCreator = new BasketSignatureGenerator();

        $this->assertNotSame(
            $signatureCreator->generateSignature(
                new Basket(0.0, 0.0, []),
                1
            ),
            $signatureCreator->generateSignature(
                new Basket(0.0, 0.0, []),
                2
            )
        );
    }
}
