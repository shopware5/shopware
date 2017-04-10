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
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\IsNewCustomerRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class IsNewCustomerRuleTest extends TestCase
{
    public function testIsNewCustomer()
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(CalculatedCart::class);

        $customer = new Customer();
        $customer->setFirstLogin(new \DateTime());

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testIsNotNewCustomer()
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(CalculatedCart::class);

        $customer = new Customer();
        $customer->setFirstLogin(
            (new \DateTime())->sub(
                new \DateInterval('P' . (int) 10 . 'D')
            )
        );

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testWithFutureDate()
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(CalculatedCart::class);

        $customer = new Customer();
        $customer->setFirstLogin(
            (new \DateTime())->add(
                new \DateInterval('P' . (int) 10 . 'D')
            )
        );

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }

    public function testWithoutCustomer()
    {
        $rule = new IsNewCustomerRule();

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue(null));

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }
}
