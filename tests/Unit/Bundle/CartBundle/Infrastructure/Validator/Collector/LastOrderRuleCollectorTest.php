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
use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\LastOrderRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\LastOrderRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\LastOrderRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class LastOrderRuleCollectorTest extends TestCase
{
    public function testWithoutRule()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);

        $collector = new LastOrderRuleCollector($connection);

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

        $collector = new LastOrderRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new LastOrderRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithLastOrder()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchColumn')
            ->will($this->returnValue('2012-01-01'));

        $customer = new Customer();
        $customer->setId(1);
        $context->expects($this->any())
            ->method('getCustomer')
            ->will($this->returnValue($customer));

        $collector = new LastOrderRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new LastOrderRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(1, $dataCollection->count());

        /** @var LastOrderRuleData $rule */
        $rule = $dataCollection->get(LastOrderRuleData::class);

        $this->assertInstanceOf(LastOrderRuleData::class, $rule);
        $this->assertEquals(
            new \DateTime('2012-01-01'),
            $rule->getLastOrderTime()
        );
    }

    public function testWithoutLastOrder()
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

        $collector = new LastOrderRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([new LastOrderRule(10)]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(1, $dataCollection->count());

        /** @var LastOrderRuleData $rule */
        $rule = $dataCollection->get(LastOrderRuleData::class);

        $this->assertInstanceOf(LastOrderRuleData::class, $rule);
        $this->assertNull($rule->getLastOrderTime());
    }
}
