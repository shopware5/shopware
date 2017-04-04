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
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\ProductAttributeRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\ProductAttributeRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ProductAttributeRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Rule\DummyProduct;

class ProductAttributeRuleCollectorTest extends TestCase
{
    public function testWithoutRule()
    {
        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createMock(Connection::class);

        $collector = new ProductAttributeRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection();

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithAttributeData()
    {
        $cart = $this->createMock(CalculatedCart::class);
        $cart->method('getLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection([
            ['attr1' => 100, 'attr2' => 200],
            ['attr1' => 200, 'attr2' => 300],
        ]);

        $collector = new ProductAttributeRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([
            new ProductAttributeRule('attr1', 100),
        ]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(1, $dataCollection->count());

        /** @var ProductAttributeRuleData $data */
        $data = $dataCollection->get(ProductAttributeRuleData::class);

        $this->assertInstanceOf(ProductAttributeRuleData::class, $data);

        $this->assertTrue($data->hasAttributeValue('attr1', 100));
        $this->assertTrue($data->hasAttributeValue('attr1', 200));
        $this->assertTrue($data->hasAttributeValue('attr2', 200));
        $this->assertTrue($data->hasAttributeValue('attr2', 300));
    }

    public function testWithoutAttributeData()
    {
        $cart = $this->createMock(CalculatedCart::class);
        $cart->method('getLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([
                    new DummyProduct('SW1'),
                    new DummyProduct('SW2'),
                ])
            ));

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection([]);

        $collector = new ProductAttributeRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([
            new ProductAttributeRule('attr1', 100),
        ]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    public function testWithEmptyCart()
    {
        $cart = $this->createMock(CalculatedCart::class);
        $cart->method('getLineItems')
            ->will($this->returnValue(
                new CalculatedLineItemCollection([])
            ));

        $context = $this->createMock(ShopContext::class);

        $connection = $this->createConnection([]);

        $collector = new ProductAttributeRuleCollector($connection);

        $dataCollection = new RuleDataCollection();

        $ruleCollection = new RuleCollection([
            new ProductAttributeRule('attr1', 100),
        ]);

        $collector->collect($ruleCollection, $cart, $context, $dataCollection);

        $this->assertSame(0, $dataCollection->count());
    }

    private function createConnection(?array $result)
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue($result));

        $query = $this->createMock(QueryBuilder::class);
        $query->expects(static::any())
            ->method('execute')
            ->will(static::returnValue($statement));

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::any())
            ->method('createQueryBuilder')
            ->will(static::returnValue($query));

        return $connection;
    }
}
