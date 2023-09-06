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

namespace Shopware\Tests\Unit\Bundle\PluginInstallerBundle\UniqueIdGenerator;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\PluginInstallerBundle\Service\UniqueIdGenerator\UniqueIdGenerator;

class UniqueIdGeneratorTest extends TestCase
{
    /**
     * Tests if an existing unique id is returned and not stored again.
     */
    public function testReturnUniqueIdFromDb(): void
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne', 'executeStatement'])
            ->getMock();

        $connectionMock->expects(static::once())
            ->method('fetchOne')
            ->willReturn('s:32:"xErV4zUsI28DVKfayeIB6rqIOBjR8OEB";');

        $connectionMock->expects(static::exactly(0))
            ->method('executeStatement')
            ->willReturn(true);

        $dbStorage = new UniqueIdGenerator(
            $connectionMock
        );

        static::assertEquals('xErV4zUsI28DVKfayeIB6rqIOBjR8OEB', $dbStorage->getUniqueId());
    }

    /**
     * Tests if all necessary methods are called to check for an old id in the db
     * and generate & store a new one if none exists.
     */
    public function testStoringGeneratedIdInDb(): void
    {
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchOne', 'executeStatement'])
            ->getMock();

        $connectionMock->expects(static::exactly(2))
            ->method('fetchOne')
            ->willReturn(null);

        $connectionMock->expects(static::once())
            ->method('executeStatement')
            ->willReturn(true);

        $dbStorage = new UniqueIdGenerator(
            $connectionMock
        );

        static::assertNotNull($dbStorage->getUniqueId());
    }
}
