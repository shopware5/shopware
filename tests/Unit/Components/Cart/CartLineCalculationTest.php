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

namespace Shopware\Tests\Unit\Components\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity;
use Shopware\Components\Cart\NetRounding\RoundLineAfterTax;

class CartLineCalculationTest extends TestCase
{
    public function testRoundingAfterQuantity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.0, 19, 1);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingDownAfterQuantity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.111, 11.1, 1);
        static::assertEquals(1.23321, $lineValue);
    }

    public function testRoundingUpAfterQuantity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 1);
        static::assertEquals(1.5876000000000001, $lineValue);
    }

    public function testRoundingAfterQuantityMultipleQuantity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 10);
        static::assertEquals(15.88, $lineValue);
    }

    public function testRoundingAfterQuantityNoQuanity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 0);
        static::assertEquals(0.0, $lineValue);
    }

    public function testRoundingAfterQuantityDefaultQuanity(): void
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.0, 19);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingafterTax(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.0, 19, 1);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingDownafterTax(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.111, 11.1, 1);
        static::assertEquals(1.23, $lineValue);
    }

    public function testRoundingUpafterTax(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.26, 26, 1);
        static::assertEquals(1.59, $lineValue);
    }

    public function testRoundingafterTaxMultipleQuantity(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.26, 26, 10);
        static::assertEquals(15.90, $lineValue);
    }

    public function testRoundingafterTaxNoQuanity(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.26, 26, 0);
        static::assertEquals(0.0, $lineValue);
    }

    public function testRoundingafterTaxDefaultQuanity(): void
    {
        $calculator = new RoundLineAfterTax();

        $lineValue = $calculator->round(1.0, 19);
        static::assertEquals(1.19, $lineValue);
    }
}
