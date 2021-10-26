<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Bundle\AttributeBundle\Repository;

use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Repository\OrderRepository;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Models\Order\Order;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper\ProgressHelper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class OrderRepositoryTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testGetMapping(): void
    {
        $mapping = $this->getOrderRepository()->getMapping();

        $expectedFormat = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd';
        $expectedTextField = ['type' => 'text', 'fielddata' => true];

        static::assertSame($expectedFormat, $mapping['properties']['orderTime']['format']);
        static::assertSame($expectedTextField, $mapping['properties']['articleNumber']);
    }

    /**
     * @group elasticSearch
     */
    public function testOrderOrder(): void
    {
        $searchCriteria = new SearchCriteria(Order::class);
        $searchCriteria->sortings = [[
            'property' => 'orderTime',
            'direction' => 'DESC',
        ]];

        $sql = file_get_contents(__DIR__ . '/_fixtures/orders.sql');
        static::assertIsString($sql);
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $indexer = Shopware()->Container()->get('shopware_es_backend.indexer');
        $indexer->index(new ProgressHelper());

        $searchResult = $this->getOrderRepository()->search($searchCriteria);
        static::assertSame(18, $searchResult->getCount());
        $orders = $searchResult->getData();

        $orderTimes = array_map(function ($order) {
            return $order['orderTime'];
        }, $orders);

        /** @var DateTimeInterface $firstOrderTime */
        $firstOrderTime = array_shift($orderTimes);
        $orderTimeStamp = $firstOrderTime->getTimestamp();

        /** @var DateTimeInterface|null $orderTime */
        foreach ($orderTimes as $orderTime) {
            if ($orderTime === null) {
                continue;
            }

            static::assertLessThan($orderTimeStamp, $orderTime->getTimestamp());

            $orderTimeStamp = $orderTime->getTimestamp();
        }
    }

    /**
     * @group elasticSearch
     */
    public function testOrderContainsProductNumbers(): void
    {
        $searchCriteria = new SearchCriteria(Order::class);
        $searchCriteria->sortings = [[
            'property' => 'orderTime',
            'direction' => 'DESC',
        ]];

        $sql = file_get_contents(__DIR__ . '/_fixtures/orders.sql');
        static::assertIsString($sql);
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $indexer = Shopware()->Container()->get('shopware_es_backend.indexer');
        $indexer->index(new ProgressHelper());

        $searchResult = $this->getOrderRepository()->search($searchCriteria);
        $orders = $searchResult->getData();

        foreach ($orders as $order) {
            static::assertArrayHasKey('articleNumber', $order);
        }
    }

    private function getOrderRepository(): OrderRepository
    {
        return $this->getContainer()->get(OrderRepository::class);
    }
}
