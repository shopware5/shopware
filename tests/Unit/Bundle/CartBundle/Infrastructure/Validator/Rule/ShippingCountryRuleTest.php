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
use Shopware\Bundle\CartBundle\Domain\Delivery\ShippingLocation;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ShippingCountryRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Country\Country;

class ShippingCountryRuleTest extends TestCase
{
    public function testEquals()
    {
        $rule = new ShippingCountryRule([1], ShippingCountryRule::OPERATOR_EQ);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setId(1);

        $context->expects($this->any())
            ->method('getShippingLocation')
            ->will($this->returnValue(ShippingLocation::createFromCountry($country)));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testNotEquals()
    {
        $rule = new ShippingCountryRule([1], ShippingCountryRule::OPERATOR_NEQ);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setId(1);

        $context->expects($this->any())
            ->method('getShippingLocation')
            ->will($this->returnValue(ShippingLocation::createFromCountry($country)));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testEqualsWithMultipleCountries()
    {
        $rule = new ShippingCountryRule([1, 2, 3], ShippingCountryRule::OPERATOR_EQ);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setId(2);

        $context->expects($this->any())
            ->method('getShippingLocation')
            ->will($this->returnValue(ShippingLocation::createFromCountry($country)));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testNotEqualsWithMultipleCountries()
    {
        $rule = new ShippingCountryRule([1, 2, 3], ShippingCountryRule::OPERATOR_NEQ);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setId(2);

        $context->expects($this->any())
            ->method('getShippingLocation')
            ->will($this->returnValue(ShippingLocation::createFromCountry($country)));

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
        $rule = new ShippingCountryRule([1, 2, 3], $operator);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $country = new Country();
        $country->setId(2);

        $context->expects($this->any())
            ->method('getShippingLocation')
            ->will($this->returnValue(ShippingLocation::createFromCountry($country)));

        $rule->match($cart, $context, new RuleDataCollection());
    }

    public function unsupportedOperators()
    {
        return [
            [true],
            [false],
            [''],
            [ShippingCountryRule::OPERATOR_GTE],
            [ShippingCountryRule::OPERATOR_LTE],
        ];
    }
}
