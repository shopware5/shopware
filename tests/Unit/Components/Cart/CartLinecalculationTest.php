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

namespace Shopware\tests\Unit\Components\Cart;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity;
use Shopware\Components\Cart\NetRounding\RoundLineAfterTax;

class CartLinecalculationTest extends TestCase
{
    public function testRoundingAfterQuantity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.0, 19, 1);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingDownAfterQuantity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.111, 11.1, 1);
        static::assertEquals(1.23321, $lineValue);
    }

    public function testRoundingUpAfterQuantity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 1);
        static::assertEquals(1.5876000000000001, $lineValue);
    }

    public function testRoundingAfterQuantityMultipleQuantity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 10);
        static::assertEquals(15.88, $lineValue);
    }

    public function testRoundingAfterQuantityNoQuanity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.26, 26, 0);
        static::assertEquals(0.0, $lineValue);
    }

    public function testRoundingAfterQuantityDefaultQuanity()
    {
        $calculator = new RoundLineAfterQuantity();

        $lineValue = $calculator->round(1.0, 19);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingafterTax()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.0, 19, 1);
        static::assertEquals(1.19, $lineValue);
    }

    public function testRoundingDownafterTax()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.111, 11.1, 1);
        static::assertEquals(1.23, $lineValue);
    }

    public function testRoundingUpafterTax()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.26, 26, 1);
        static::assertEquals(1.59, $lineValue);
    }

    public function testRoundingafterTaxMultipleQuantity()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.26, 26, 10);
        static::assertEquals(15.90, $lineValue);
    }

    public function testRoundingafterTaxNoQuanity()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.26, 26, 0);
        static::assertEquals(0.0, $lineValue);
    }

    public function testRoundingafterTaxDefaultQuanity()
    {
        $calculator = new RoundLineafterTax();

        $lineValue = $calculator->round(1.0, 19);
        static::assertEquals(1.19, $lineValue);
    }
}
