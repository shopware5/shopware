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

namespace Shopware\Tests\Functional\Components\Cart;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Cart\BasketQueryHelper;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\Cart\Struct\Price;

class BasketQueryHelperTest extends TestCase
{
    /**
     * @var BasketQueryHelper
     */
    private $queryHelper;

    public function setUp(): void
    {
        $this->queryHelper = new BasketQueryHelper(
            Shopware()->Container()->get('dbal_connection')
        );
    }

    public function tearDown(): void
    {
        $this->queryHelper = null;
    }

    public function test_getPositionPricesQuery()
    {
        $discountContext = $this->getDiscountContext();
        $result = $this->queryHelper->getPositionPricesQuery($discountContext);

        $expectedSql = 'SELECT basket.price as end_price, basket.netprice as net_price, basket.tax_rate, basket.quantity FROM s_order_basket basket WHERE (basket.modus = 0) AND (basket.sessionID = :session) AND (basket.tax_rate != 0)';

        static::assertInstanceOf(QueryBuilder::class, $result);
        static::assertSame($expectedSql, $result->getSQL());
    }

    public function test_getInsertDiscountAttributeQuery()
    {
        $discountContext = $this->getDiscountContext();
        $result = $this->queryHelper->getInsertDiscountAttributeQuery($discountContext);

        $expectedSql = 'INSERT INTO s_order_basket_attributes (basketID) VALUES(:basketId)';

        static::assertInstanceOf(QueryBuilder::class, $result);
        static::assertSame($expectedSql, $result->getSQL());
    }

    public function test_getInsertDiscountQuery()
    {
        $discountContext = $this->getDiscountContext();
        $discountContext->setPrice($this->getPrice());

        $result = $this->queryHelper->getInsertDiscountQuery($discountContext);

        $expectedSql = 'INSERT INTO s_order_basket (sessionID, articlename, articleID, ordernumber, quantity, price, netprice, tax_rate, datum, modus, currencyFactor) VALUES(:sessionId, :articleName, :articleName, :ordernumber, :quantity, :price, :netPrice, :taxRate, :datum, :mode, :currencyFactor)';

        static::assertInstanceOf(QueryBuilder::class, $result);
        static::assertSame($expectedSql, $result->getSQL());
    }

    public function test_getLastInsertId()
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->method('lastInsertId')->willReturn(111);

        $reflectionBasketQueryHelper = new \ReflectionClass(BasketQueryHelper::class);
        $connectionProperty = $reflectionBasketQueryHelper->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue(
            $this->queryHelper,
            $connectionMock
        );

        $result = $this->queryHelper->getLastInsertId();

        static::assertSame(111, $result);
    }

    /**
     * @return Price
     */
    private function getPrice()
    {
        return new Price(100.00, 84.04, 19, 19.00);
    }

    /**
     * @return DiscountContext
     */
    private function getDiscountContext()
    {
        return new DiscountContext('sessionId', 1, 10, 'DISCOUNT', '08154711', 4, 1, false);
    }
}
