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

namespace Shopware\Tests\Unit\Components\Emotion;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Emotion\DeviceConfiguration;

class DeviceConfigurationTest extends TestCase
{
    public function testNoneExistingEmotion()
    {
        $service = new DeviceConfiguration($this->createQueryMock([]));
        $emotions = $service->get(1);
        static::assertEmpty($emotions);
    }

    public function testEmotionsWithDifferentPositions()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => 4, 'devices' => '', 'shopIds' => ''],
            ['id' => 2, 'position' => 3, 'devices' => '', 'shopIds' => ''],
            ['id' => 3, 'position' => 1, 'devices' => '', 'shopIds' => ''],
            ['id' => 4, 'position' => 2, 'devices' => '', 'shopIds' => ''],
        ]));
        $emotions = $service->get(1);
        static::assertEquals(
            [
                ['id' => 3, 'position' => 1, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 4, 'position' => 2, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 2, 'position' => 3, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 1, 'position' => 4, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
            ],
            $emotions
        );
    }

    public function testEmotionsWithNullPositions()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => null, 'devices' => '', 'shopIds' => ''],
            ['id' => 2, 'position' => null, 'devices' => '', 'shopIds' => ''],
            ['id' => 3, 'position' => null, 'devices' => '', 'shopIds' => ''],
            ['id' => 4, 'position' => null, 'devices' => '', 'shopIds' => ''],
        ]));
        $emotions = $service->get(1);
        static::assertEquals(
            [
                ['id' => 1, 'position' => null, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 2, 'position' => null, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 3, 'position' => null, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 4, 'position' => null, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
            ],
            $emotions
        );
    }

    public function testEmotionsWithSamePosition()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => 3, 'devices' => '', 'shopIds' => ''],
            ['id' => 2, 'position' => 3, 'devices' => '', 'shopIds' => ''],
            ['id' => 3, 'position' => 1, 'devices' => '', 'shopIds' => ''],
            ['id' => 4, 'position' => 1, 'devices' => '', 'shopIds' => ''],
        ]));
        $emotions = $service->get(1);
        static::assertEquals(
            [
                ['id' => 3, 'position' => 1, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 4, 'position' => 1, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 1, 'position' => 3, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
                ['id' => 2, 'position' => 3, 'devices' => '', 'devicesArray' => [''], 'shopIds' => []],
            ],
            $emotions
        );
    }

    /**
     * @param array[] $expectedResult
     *
     * @return Connection|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createQueryMock($expectedResult)
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->willReturn($expectedResult);

        $query = $this->createMock(QueryBuilder::class);
        $query->expects(static::any())
            ->method('execute')
            ->willReturn($statement);

        $query->expects(static::any())
            ->method('andWhere')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('innerJoin')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('leftJoin')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('from')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('addOrderBy')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('groupBy')
            ->willReturn($query);

        $query->expects(static::any())
            ->method('setParameter')
            ->willReturn($query);

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::any())
            ->method('createQueryBuilder')
            ->willReturn($query);

        return $connection;
    }
}
