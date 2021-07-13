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

namespace Functional\Components\Migrations;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Migrations\AbstractMigration;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;
use Shopware\Tests\Functional\Traits\MigrationTestTrait;

class Migrations_Migration1607Test extends TestCase
{
    use DatabaseTransactionBehaviour;
    use MigrationTestTrait;
    use FixtureBehaviour;

    private const MIGRATION_NUMBER = 1607;

    private AbstractMigration $migration;

    private Connection $connection;

    private int $numberOfSubshops;

    public function setUp(): void
    {
        parent::setUp();

        $this->migration = $this->getMigration(static::createStub(\PDO::class), self::MIGRATION_NUMBER);
        $this->connection = Shopware()->Container()->get('dbal_connection') ?? static::fail('No database connection available.');
        $this->numberOfSubshops = (int) $this->connection->fetchOne('SELECT COUNT(`id`) FROM `s_core_shops` WHERE `main_id` IS NOT NULL;');
    }

    /**
     * @dataProvider commentArticleConfigProvider
     *
     * @covers \Migrations_Migration1607::up
     */
    public function testDefaultValueConditionOnUpdate(string $commentArticleSql, bool $commentArticleActive): void
    {
        static::assertEmpty($this->migration->getSql());

        $this->migration->up(AbstractMigration::MODUS_UPDATE);

        static::assertNotEmpty($this->migration->getSql());

        self::executeFixture(__DIR__ . '/fixture/precondition_Migration1607.sql');
        $this->connection->executeStatement(
            $commentArticleSql,
            ['commentArticleActive' => $commentArticleActive ? 'b:1;' : 'b:0;']
        );

        foreach ($this->migration->getSql() as $sql) {
            try {
                $this->connection->executeStatement($sql);
            } catch (\Throwable $e) {
                static::fail($e->getMessage());
            }
        }

        static::assertEquals(
            $commentArticleActive ? 1 : $this->numberOfSubshops + 2,
            $this->connection->fetchOne('SELECT COUNT(`id`) FROM `s_core_config_values`;')
        );
    }

    /**
     * @return \Generator<string, array>
     */
    public function commentArticleConfigProvider(): \Generator
    {
        $sql = <<<'SQL'
INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
    SELECT `id`, 1, :commentArticleActive
    FROM s_core_config_elements
    WHERE `name` = 'commentVoucherArticle';
SQL;

        yield 'commentArticle active' => [
            $sql,
            true,
        ];

        yield 'commentArticle inactive' => [
            $sql,
            false,
        ];
    }
}
