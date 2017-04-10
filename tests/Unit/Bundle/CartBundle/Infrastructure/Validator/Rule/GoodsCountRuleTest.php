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
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Voucher\CalculatedVoucher;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherProcessor;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\GoodsCountRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;

class GoodsCountRuleTest extends TestCase
{
    public function testGteExactMatch()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testGteWithVoucher()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                    new CalculatedVoucher(
                        'Code1',
                        new LineItem(1, VoucherProcessor::TYPE_VOUCHER, 1),
                        new Price(-1, -1, new CalculatedTaxCollection(), new TaxRuleCollection())
                    ),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testGteNotMatch()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_GTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testLteExactMatch()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testLteWithVoucher()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                    new CalculatedVoucher(
                        'Code1',
                        new LineItem(1, VoucherProcessor::TYPE_VOUCHER, 1),
                        new Price(-1, -1, new CalculatedTaxCollection(), new TaxRuleCollection())
                    ),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testLteNotMatch()
    {
        $rule = new GoodsCountRule(2, GoodsCountRule::OPERATOR_LTE);

        $cart = $this->createMock(CalculatedCart::class);

        $cart->expects($this->any())
            ->method('getCalculatedLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                    new DummyProduct('SW3'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
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
        $rule = new GoodsCountRule(2, $operator);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function unsupportedOperators()
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
