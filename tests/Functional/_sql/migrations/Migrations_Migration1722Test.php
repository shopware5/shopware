<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\_sql\migrations;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\MigrationTestTrait;

class Migrations_Migration1722Test extends TestCase
{
    use ContainerTrait;
    use MigrationTestTrait;

    private const MIGRATION_NUMBER = 1722;

    /**
     * @dataProvider upTestDataProvider
     */
    public function testUp(string $currentStateValue, string $expectedResult): void
    {
        $defaultData = $this->getDatabaseResult();

        $this->installTestData($currentStateValue);

        $migration = $this->getMigration($this->createPDOConnection(), self::MIGRATION_NUMBER);

        $migration->up('update');
        $migration->up('update');

        $result = $this->getDatabaseResult();

        static::assertSame($expectedResult, $result);

        // Reset database
        $this->installTestData($defaultData);
    }

    /**
     * @return Generator<array<int,string>>
     */
    public function upTestDataProvider(): Generator
    {
        yield 'Should remove motor from start' => [
            serialize('motor;foo;bar;foo_bar;foo-bar;foo bar;'),
            serialize('foo;bar;foo_bar;foo-bar;foo bar;'),
        ];

        yield 'Should remove motor two times' => [
            serialize('motor;foo;bar;foo_bar;motor;foo-bar;foo bar;'),
            serialize('foo;bar;foo_bar;foo-bar;foo bar;'),
        ];

        yield 'Should remove motor from the middle' => [
            serialize('foo;bar;foo_bar;foo-bar;motor;foo bar;'),
            serialize('foo;bar;foo_bar;foo-bar;foo bar;'),
        ];

        yield 'Should remove motor from the end' => [
            serialize('foo;bar;foo_bar;foo-bar;foo bar;motor;'),
            serialize('foo;bar;foo_bar;foo-bar;foo bar;'),
        ];
    }

    private function getDatabaseResult(): string
    {
        $result = $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->select(['value'])
            ->from('s_core_config_elements')
            ->where('name LIKE "botBlackList"')
            ->execute()
            ->fetchOne();

        if (!\is_string($result)) {
            static::fail('Database value is not a string');
        }

        return $result;
    }

    private function installTestData(string $testData): void
    {
        $this->getContainer()->get('dbal_connection')->createQueryBuilder()
            ->update('s_core_config_elements')
            ->set('value', ':newValue')
            ->where('name = "botBlackList"')
            ->setParameter('newValue', $testData)
            ->execute();
    }
}
