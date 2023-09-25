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

namespace Shopware\Tests\Functional\Components\MultiEdit\Resource\Product;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\MultiEdit\Resource\Product\Backup;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class BackupTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

    private const FIXTURE_BACKUP_ID = 3;
    private const FIXTURE_PRODUCT_ID = 3;
    private const DIRECTORY_PLACEHOLDER = '__DIRECTORY__';

    public function testBackupRestore(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(Connection::class);
        $result = $connection->executeQuery('SELECT maxpurchase  FROM s_articles_details WHERE id = ' . self::FIXTURE_PRODUCT_ID)->fetchOne();
        static::assertNotEquals(1, (int) $result);

        $sql = file_get_contents(__DIR__ . '/fixtures/fixtures.sql');
        static::assertIsString($sql);
        $sql = str_replace(self::DIRECTORY_PLACEHOLDER, $this->getContainer()->getParameter('kernel.project_dir'), $sql);
        $this->getContainer()->get('dbal_connection')->executeStatement($sql);

        $this->getContainer()->get(Backup::class)->restore(self::FIXTURE_BACKUP_ID, 0);

        $result = $connection->executeQuery('SELECT maxpurchase  FROM s_articles_details WHERE id = ' . self::FIXTURE_PRODUCT_ID)->fetchOne();
        static::assertEquals(1, (int) $result);
    }
}
