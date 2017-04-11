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
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ShopRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Shop\Shop;

class ShopRuleTest extends TestCase
{
    public function testEqualsWithSingleShop()
    {
        $rule = new ShopRule([1], ShopRule::OPERATOR_EQ);

        $shop = new Shop();
        $shop->setId(1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertTrue($rule->match($cart, $context, new RuleDataCollection()));
    }

    public function testEqualsWithMultipleShops()
    {
        $rule = new ShopRule([2, 3, 4, 1], ShopRule::OPERATOR_EQ);

        $shop = new Shop();
        $shop->setId(3);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testEqualsNotMatchWithSingleShop()
    {
        $rule = new ShopRule([11], ShopRule::OPERATOR_EQ);

        $shop = new Shop();
        $shop->setId(1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testEqualsNotMatchWithMultipleShops()
    {
        $rule = new ShopRule([2, 3, 4, 1], ShopRule::OPERATOR_EQ);

        $shop = new Shop();
        $shop->setId(11);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testNotEqualsWithSingleShop()
    {
        $rule = new ShopRule([1], ShopRule::OPERATOR_NEQ);

        $shop = new Shop();
        $shop->setId(1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertFalse($rule->match($cart, $context, new RuleDataCollection()));
    }

    public function testNotEqualsWithMultipleShops()
    {
        $rule = new ShopRule([2, 3, 4, 1], ShopRule::OPERATOR_NEQ);

        $shop = new Shop();
        $shop->setId(3);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertFalse($rule->match($cart, $context, new RuleDataCollection()));
    }

    public function testNotEqualsNotMatchWithSingleShop()
    {
        $rule = new ShopRule([11], ShopRule::OPERATOR_NEQ);

        $shop = new Shop();
        $shop->setId(1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertTrue($rule->match($cart, $context, new RuleDataCollection()));
    }

    public function testNotEqualsNotMatchWithMultipleShops()
    {
        $rule = new ShopRule([2, 3, 4, 1], ShopRule::OPERATOR_NEQ);

        $shop = new Shop();
        $shop->setId(11);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $this->assertTrue($rule->match($cart, $context, new RuleDataCollection()));
    }

    /**
     * @dataProvider unsupportedOperators
     *
     * @expectedException \Shopware\Bundle\CartBundle\Domain\Validator\Exception\UnsupportedOperatorException
     *
     * @param string $operator
     */
    public function testUnsupportedOperators(string $operator)
    {
        $rule = new ShopRule([1], $operator);
        $shop = new Shop();
        $shop->setId(1);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getShop')
            ->will($this->returnValue($shop));

        $rule->match($cart, $context, new RuleDataCollection());
    }

    public function unsupportedOperators()
    {
        return [
            [true],
            [false],
            [''],
            [Rule::OPERATOR_GTE],
            [Rule::OPERATOR_LTE],
        ];
    }
}
