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

namespace Shopware\Tests\Functional\Bundle\PluginInstallerBundle\Service;

use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginInstaller;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin as PluginModel;
use Shopware\Tests\Functional\Bundle\PluginInstallerBundle\Fixture\MyPlugin\MyPlugin;

class PluginInstallerTest extends TestCase
{
    public function testSnippetsFromMenuGetRemovedOnUninstall(): void
    {
        $connection = Shopware()->Container()->get('dbal_connection');

        $entityManager = $this->createMock(ModelManager::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $databaseHandler = $this->createMock(DatabaseHandler::class);
        $requirementValidator = $this->createMock(RequirementValidator::class);

        $pdo = $this->createMock(PDO::class);

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('getPlugins')->willReturn([
            'TestPlugin' => new MyPlugin(true, 'noop'),
        ]);

        $pluginInstaller = new PluginInstaller(
            $entityManager,
            $databaseHandler,
            $requirementValidator,
            $pdo,
            new \Enlight_Event_EventManager(),
            ['ShopwarePlugins' => __DIR__ . '/Fixtures'],
            new ShopwareReleaseStruct('1.0.0', '', '___VERSION___'),
            new NullLogger(),
            $kernel
        );

        $plugin = new PluginModel();
        $plugin->setId(5);
        $plugin->setName('TestPlugin');

        $connection->insert('s_core_menu', [
            'name' => 'bla',
            'controller' => 'TestPlugin',
            'action' => 'Index',
            'pluginID' => 5,
        ]);

        $connection->insert('s_core_snippets', [
            'namespace' => 'backend/index/view/main',
            'name' => 'TestPlugin',
        ]);

        $pluginInstaller->uninstallPlugin($plugin, false);

        static::assertFalse($connection->fetchOne('SELECT 1 FROM s_core_menu WHERE controller = "TestPlugin"'));
        static::assertFalse($connection->fetchOne('SELECT 1 FROM s_core_snippets WHERE name = "TestPlugin"'));
    }
}
