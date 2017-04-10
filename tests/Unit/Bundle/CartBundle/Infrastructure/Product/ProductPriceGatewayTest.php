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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinitionCollection;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Product\ProductPriceGateway;
use Shopware\Bundle\StoreFrontBundle\Gateway\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\Hydrator\TaxHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\Generator;

class ProductPriceGatewayTest extends \PHPUnit\Framework\TestCase
{
    const QUERY_TO_UNLIMITED = 'beliebig';

    public function testNoProductPricesDefined()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);
        static::assertEquals(new PriceDefinitionCollection(), $prices);
    }

    public function testReturnsPriceDefinitionIndexedByNumber()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK1', 1, self::QUERY_TO_UNLIMITED, 20.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(20.10, new TaxRuleCollection([new TaxRule(19)])),
            ]),
            $prices
        );
    }

    public function testCurrentCustomerGroupPricesHasHigherPriority()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                    PriceQueryRow::create('EK1', 1, self::QUERY_TO_UNLIMITED, 20.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(20.10, new TaxRuleCollection([new TaxRule(19)])),
            ]),
            $prices
        );
    }

    public function testProductsWithFallbackCustomerGroupPrice()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                    PriceQueryRow::create('EK1', 1, self::QUERY_TO_UNLIMITED, 20.10, 19),
                ],
                'SW2' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 1),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 1),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(20.10, new TaxRuleCollection([new TaxRule(19)])),
                'SW2' => new PriceDefinition(5.10, new TaxRuleCollection([new TaxRule(19)])),
            ]),
            $prices
        );
    }

    public function testLastGraduatedPriceOfCurrentCustomerGroup()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                    PriceQueryRow::create('EK1', 1, 3, 20.10, 19),
                    PriceQueryRow::create('EK1', 4, self::QUERY_TO_UNLIMITED, 15.10, 19),
                ],
                'SW2' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 4),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 2),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(15.10, new TaxRuleCollection([new TaxRule(19)]), 4),
                'SW2' => new PriceDefinition(5.10, new TaxRuleCollection([new TaxRule(19)]), 2),
            ]),
            $prices
        );
    }

    public function testLineItemPriceAppliedToPriceDefinition()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 1.10, 19),
                ],
                'SW2' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                ],
                'SW3' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 10.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 2),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 3),
            new LineItem('SW3', ProductProcessor::TYPE_PRODUCT, 4),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(1.10, new TaxRuleCollection([new TaxRule(19)]), 2),
                'SW2' => new PriceDefinition(5.10, new TaxRuleCollection([new TaxRule(19)]), 3),
                'SW3' => new PriceDefinition(10.10, new TaxRuleCollection([new TaxRule(19)]), 4),
            ]),
            $prices
        );
    }

    public function testUseFallbackPriceWithGraduation()
    {
        $gateway = new ProductPriceGateway(
            $this->createDatabaseMock([
                'SW1' => [
                    PriceQueryRow::create('EK2', 1, 2, 1.10, 19),
                    PriceQueryRow::create('EK2', 3, 4, 2.10, 19),
                    PriceQueryRow::create('EK2', 5, self::QUERY_TO_UNLIMITED, 3.10, 19),
                    PriceQueryRow::create('EK3', 1, self::QUERY_TO_UNLIMITED, 20.10, 19),
                ],
                'SW2' => [
                    PriceQueryRow::create('EK2', 1, self::QUERY_TO_UNLIMITED, 5.10, 19),
                ],
            ]),
            $this->createMock(FieldHelper::class),
            new TaxHydrator()
        );

        $collection = new LineItemCollection([
            new LineItem('SW1', ProductProcessor::TYPE_PRODUCT, 3),
            new LineItem('SW2', ProductProcessor::TYPE_PRODUCT, 2),
        ]);

        $context = Generator::createContext(
            $this->createCustomerGroup('EK1'), //current customer group
            $this->createCustomerGroup('EK2')  //fallback customer group
        );

        $prices = $gateway->get($collection, $context);

        static::assertEquals(
            new PriceDefinitionCollection([
                'SW1' => new PriceDefinition(2.10, new TaxRuleCollection([new TaxRule(19)]), 3),
                'SW2' => new PriceDefinition(5.10, new TaxRuleCollection([new TaxRule(19)]), 2),
            ]),
            $prices
        );
    }

    /**
     * @param array[] $queryResult
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function createDatabaseMock($queryResult)
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue($queryResult));

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

    /**
     * @param string $key
     *
     * @return Group
     */
    private function createCustomerGroup($key)
    {
        $group = new Group();
        $group->setKey($key);

        return $group;
    }
}

class PriceQueryRow
{
    public static function create(
        $customerGroupKey,
        $from,
        $to,
        $price,
        $taxRate,
        $taxId = null,
        $taxName = null
    ) {
        return [
            'price_customer_group_key' => $customerGroupKey,
            'price_from_quantity' => $from,
            'price_to_quantity' => $to,
            'price_net' => $price,
            '__tax_id' => $taxId,
            '__tax_tax' => $taxRate,
            '__tax_description' => $taxName,
        ];
    }
}
