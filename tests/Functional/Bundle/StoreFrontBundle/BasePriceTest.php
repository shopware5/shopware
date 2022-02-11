<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;

class BasePriceTest extends TestCase
{
    public function testHigherReferenceUnit(): void
    {
        $number = 'Higher-Reference-Unit';
        $context = $this->getContext();

        $taxRules = $context->getTaxRules();
        $data = $this->helper->getSimpleProduct(
            $number,
            array_shift($taxRules),
            $context->getCurrentCustomerGroup()
        );

        $data['mainDetail'] = array_merge($data['mainDetail'], [
            'purchaseUnit' => 0.5,
            'referenceUnit' => 1,
        ]);
        $data['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];

        $this->helper->createProduct($data);

        $prices = $this->helper->getListProduct($number, $context)->getPrices();
        $first = array_shift($prices);
        static::assertInstanceOf(Price::class, $first);
        static::assertEquals(100, $first->getCalculatedPrice());
        static::assertEquals(200, $first->getCalculatedReferencePrice());

        $last = array_pop($prices);
        static::assertInstanceOf(Price::class, $last);
        static::assertEquals(50, $last->getCalculatedPrice());
        static::assertEquals(100, $last->getCalculatedReferencePrice());
    }

    public function testHigherPurchaseUnit(): void
    {
        $number = 'Higher-Purchase-Unit';
        $context = $this->getContext();

        $taxRules = $context->getTaxRules();
        $data = $this->helper->getSimpleProduct(
            $number,
            array_shift($taxRules),
            $context->getCurrentCustomerGroup()
        );

        $data['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];
        $data['mainDetail'] = array_merge($data['mainDetail'], [
            'purchaseUnit' => 0.5,
            'referenceUnit' => 0.1,
        ]);

        $this->helper->createProduct($data);
        $prices = $this->helper->getListProduct($number, $context)->getPrices();
        $first = array_shift($prices);
        static::assertInstanceOf(Price::class, $first);
        static::assertEquals(100, $first->getCalculatedPrice());
        static::assertEquals(20, $first->getCalculatedReferencePrice());

        $last = array_pop($prices);
        static::assertInstanceOf(Price::class, $last);
        static::assertEquals(50, $last->getCalculatedPrice());
        static::assertEquals(10, $last->getCalculatedReferencePrice());
    }
}
