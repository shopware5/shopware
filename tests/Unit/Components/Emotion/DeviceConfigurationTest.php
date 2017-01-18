<?php

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
        $this->assertEmpty($emotions);
    }

    public function testEmotionsWithDifferentPositions()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => 4, 'devices' => ''],
            ['id' => 2, 'position' => 3, 'devices' => ''],
            ['id' => 3, 'position' => 1, 'devices' => ''],
            ['id' => 4, 'position' => 2, 'devices' => '']
        ]));
        $emotions = $service->get(1);
        $this->assertEquals(
            [
                ['id' => 3, 'position' => 1, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 4, 'position' => 2, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 2, 'position' => 3, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 1, 'position' => 4, 'devices' => '', 'devicesArray' => ['']]
            ],
            $emotions
        );
    }

    public function testEmotionsWithNullPositions()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => null, 'devices' => ''],
            ['id' => 2, 'position' => null, 'devices' => ''],
            ['id' => 3, 'position' => null, 'devices' => ''],
            ['id' => 4, 'position' => null, 'devices' => '']
        ]));
        $emotions = $service->get(1);
        $this->assertEquals(
            [
                ['id' => 1, 'position' => null, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 2, 'position' => null, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 3, 'position' => null, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 4, 'position' => null, 'devices' => '', 'devicesArray' => ['']]
            ],
            $emotions
        );
    }

    public function testEmotionsWithSamePosition()
    {
        $service = new DeviceConfiguration($this->createQueryMock([
            ['id' => 1, 'position' => 3, 'devices' => ''],
            ['id' => 2, 'position' => 3, 'devices' => ''],
            ['id' => 3, 'position' => 1, 'devices' => ''],
            ['id' => 4, 'position' => 1, 'devices' => '']
        ]));
        $emotions = $service->get(1);
        $this->assertEquals(
            [
                ['id' => 3, 'position' => 1, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 4, 'position' => 1, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 1, 'position' => 3, 'devices' => '', 'devicesArray' => ['']],
                ['id' => 2, 'position' => 3, 'devices' => '', 'devicesArray' => ['']],
            ],
            $emotions
        );
    }

    /**
     * @param array[] $expectedResult
     * @return Connection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createQueryMock($expectedResult)
    {
        $statement = $this->createMock(Statement::class);
        $statement->expects(static::any())
            ->method('fetchAll')
            ->will(static::returnValue($expectedResult));

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
