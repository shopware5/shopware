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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Collector;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\OrderCountRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\OrderCountRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\OrderCountRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class OrderCountRuleCollectorTest extends TestCase
{
    public function testWithoutRule()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);

        $collector = new OrderCountRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection();

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithoutCustomer()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);

        $collector = new OrderCountRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new OrderCountRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithOrders()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchColumn')
            ->will($this->returnValue(1));

        $customer = new Customer();
        $customer->setId(1);
        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $collector = new OrderCountRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new OrderCountRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(1, $dataCollection->count());

        /** @var OrderCountRuleData $rule */
        $rule = $dataCollection->get(OrderCountRuleData::class);

        $this->assertInstanceOf(OrderCountRuleData::class, $rule);
        $this->assertSame(1, $rule->getOrderCount());
    }

    public function testWithoutOrders()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchColumn')
            ->will($this->returnValue(null));

        $customer = new Customer();
        $customer->setId(1);
        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $collector = new OrderCountRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new OrderCountRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(1, $dataCollection->count());

        /** @var OrderCountRuleData $rule */
        $rule = $dataCollection->get(OrderCountRuleData::class);

        $this->assertInstanceOf(OrderCountRuleData::class, $rule);
        $this->assertSame(0, $rule->getOrderCount());
    }
}
