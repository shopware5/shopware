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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Price\CartPrice;
use Shopware\Bundle\CartBundle\Domain\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Rule\OrderAmountRule;
use Shopware\Bundle\StoreFrontBundle\Common\StructCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;

class OrderAmountRuleTest extends TestCase
{
    public function testRuleWithGteAndExactAmount(): void
    {
        $rule = new OrderAmountRule(100, OrderAmountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(100, 100, 100, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function testRuleWithGteAndGreaterAmount(): void
    {
        $rule = new OrderAmountRule(100, OrderAmountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(200, 200, 200, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function testRuleNotMatchWithGte(): void
    {
        $rule = new OrderAmountRule(100, OrderAmountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(50, 50, 50, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function testRuleWithLteAndExactAmount(): void
    {
        $rule = new OrderAmountRule(100, \Shopware\Bundle\CartBundle\Infrastructure\Rule\OrderAmountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(100, 100, 100, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function testRuleWithLteAndGreaterAmount(): void
    {
        $rule = new OrderAmountRule(100, \Shopware\Bundle\CartBundle\Infrastructure\Rule\OrderAmountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(50, 50, 50, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function testRuleNotMatchWithLte(): void
    {
        $rule = new OrderAmountRule(100, OrderAmountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(150, 150, 150, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    /**
     * @dataProvider unsupportedOperators
     *
     * @expectedException \Shopware\Bundle\CartBundle\Domain\Rule\Exception\UnsupportedOperatorException
     *
     * @param string $operator
     */
    public function testUnsupportedOperators(string $operator): void
    {
        $rule = new \Shopware\Bundle\CartBundle\Infrastructure\Rule\OrderAmountRule(100, $operator);

        $cart = $this->createMock(CalculatedCart::class);

        $price = new CartPrice(150, 150, 150, new CalculatedTaxCollection(), new TaxRuleCollection());
        $cart->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue($price));

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new StructCollection())->matches()
        );
    }

    public function unsupportedOperators(): array
    {
        return [
            [true],
            [false],
            [''],
            [Rule::OPERATOR_EQ],
            [Rule::OPERATOR_NEQ],
        ];
    }
}
