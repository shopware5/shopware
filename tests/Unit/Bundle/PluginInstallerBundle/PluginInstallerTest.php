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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Kernel;

class PluginInstallerTest extends TestCase
{
    public function testRefreshPluginList()
    {
        $dateTime = new DateTimeImmutable();

        $expectedData = [
            'namespace' => 'ShopwarePlugins',
            'version' => '1.0.0',
            'author' => null,
            'name' => 'TestPlugin',
            'link' => null,
            'label' => 'TestPlugin',
            'description' => null,
            'capability_update' => true,
            'capability_install' => true,
            'capability_enable' => true,
            'capability_secure_uninstall' => true,
            'refresh_date' => $dateTime,
            'translations' => '{"en":{"label":"TestPlugin"}}',
            'changes' => null,
            'added' => $dateTime,
        ];

        $connection = $this->createMock(Connection::class);
        $connection->expects(static::once())->method('fetchAssoc')->willReturn(null);
        $connection->expects(static::once())->method('insert')->with('s_core_plugins', $expectedData, [
            'added' => 'datetime',
            'refresh_date' => 'datetime',
        ]);

        $entityManager = $this->createMock(ModelManager::class);
        $entityManager->expects(static::any())->method('getConnection')->willReturn($connection);

        $databaseHandler = $this->createMock(DatabaseHandler::class);
        $requirementValidator = $this->createMock(RequirementValidator::class);

        $statement = $this->createMock(Statement::class);
        $statement->expects(static::once())->method('fetchAll')->willReturn([]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects(static::once())->method('query')->willReturn($statement);

        $kernel = new Kernel('testing', true);
        $releaseArray = $kernel->getRelease();

        $pluginInstaller = new PluginInstaller(
            $entityManager,
            $databaseHandler,
            $requirementValidator,
            $pdo,
            new \Enlight_Event_EventManager(),
            ['ShopwarePlugins' => __DIR__ . '/Fixtures'],
            new ShopwareReleaseStruct($releaseArray['version'], $releaseArray['version_text'], $releaseArray['revision']),
            new NullLogger()
        );

        $pluginInstaller->refreshPluginList($dateTime);
    }
}
