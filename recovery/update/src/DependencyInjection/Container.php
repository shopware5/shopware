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

namespace Shopware\Recovery\Update\DependencyInjection;

use Shopware\Components\Migrations\Manager as MigrationManager;
use Shopware\Recovery\Common\DependencyInjection\Container as BaseContainer;
use Shopware\Recovery\Common\DumpIterator;
use Shopware\Recovery\Common\HttpClient\CurlClient;
use Shopware\Recovery\Common\SystemLocker;
use Shopware\Recovery\Update\Cleanup;
use Shopware\Recovery\Update\CleanupFilesFinder;
use Shopware\Recovery\Update\Controller\BatchController;
use Shopware\Recovery\Update\Controller\CleanupController;
use Shopware\Recovery\Update\Controller\RequirementsController;
use Shopware\Recovery\Update\DummyPluginFinder;
use Shopware\Recovery\Update\FilePermissionChanger;
use Shopware\Recovery\Update\FilesystemFactory;
use Shopware\Recovery\Update\PathBuilder;
use Shopware\Recovery\Update\PluginCheck;
use Shopware\Recovery\Update\StoreApi;
use Shopware\Recovery\Update\Utils;

class Container extends BaseContainer
{
    public function setup(\Pimple\Container $container)
    {
        $backupDir = SW_PATH . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'auto_update';

        $me = $this;

        $container['shopware.version'] = function () use ($me) {
            $version = trim(file_get_contents(UPDATE_ASSET_PATH . DIRECTORY_SEPARATOR . 'version'));

            return $version;
        };

        $container['db'] = function () use ($me) {
            $conn = Utils::getConnection(SW_PATH);

            return $conn;
        };

        $container['filesystem.factory'] = function () use ($me) {
            $updateConfig = $me->getParameter('update.config');
            $ftp = (isset($updateConfig['ftp_credentials'])) ? $updateConfig['ftp_credentials'] : [];

            return new FilesystemFactory(SW_PATH, $ftp);
        };

        $container['path.builder'] = function () use ($me, $backupDir) {
            $baseDir = SW_PATH;
            $updateDir = UPDATE_FILES_PATH;

            return new PathBuilder($baseDir, $updateDir, $backupDir);
        };

        $container['migration.manager'] = function () use ($me) {
            $migrationPath = UPDATE_ASSET_PATH . DIRECTORY_SEPARATOR . 'migrations' . DIRECTORY_SEPARATOR;
            $db = $me->get('db');

            $migrationManger = new MigrationManager($db, $migrationPath);

            return $migrationManger;
        };

        $container['dump'] = function () use ($me) {
            $snippetsSql = UPDATE_ASSET_PATH . DIRECTORY_SEPARATOR . 'snippets.sql';
            $snippetsSql = file_exists($snippetsSql) ? $snippetsSql : null;

            if (!$snippetsSql) {
                return null;
            }

            return new DumpIterator($snippetsSql);
        };

        $container['app'] = function () use ($me) {
            $slimOptions = $me->getParameter('slim');
            $slim = new \Slim\Slim($slimOptions);

            $me->set('slim.request', $slim->request());
            $me->set('slim.response', $slim->response());

            return $slim;
        };

        $container['http-client'] = function () {
            return new CurlClient();
        };

        $container['store.api'] = function () use ($me) {
            return new StoreApi(
                $me->get('http-client'),
                $me->getParameter('storeapi.endpoint')
            );
        };

        $container['plugin.check'] = function () use ($me) {
            return new PluginCheck(
                $me->get('store.api'),
                $me->get('db'),
                $me->get('shopware.version')
            );
        };

        $container['dummy.plugin.finder'] = function () {
            return new DummyPluginFinder(SW_PATH);
        };

        $container['cleanup.files.finder'] = function () {
            return new CleanupFilesFinder(SW_PATH);
        };

        $container['system.locker'] = function () {
            return new SystemLocker(
                SW_PATH . DIRECTORY_SEPARATOR . 'recovery' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'install.lock'
            );
        };

        $container['controller.batch'] = function () use ($me) {
            return new BatchController(
                $me->get('slim.request'),
                $me->get('slim.response'),
                $me
            );
        };

        $container['controller.requirements'] = function () use ($me) {
            return new RequirementsController(
                $me->get('slim.request'),
                $me->get('slim.response'),
                $me,
                $me->get('app')
            );
        };

        $container['controller.cleanup'] = function () use ($me, $backupDir) {
            return new CleanupController(
                $me->get('slim.request'),
                $me->get('slim.response'),
                $me->get('dummy.plugin.finder'),
                $me->get('cleanup.files.finder'),
                $me->get('shopware.update.cleanup'),
                $me->get('app'),
                SW_PATH,
                $me->get('db'),
                $backupDir
            );
        };

        $container['shopware.container'] = function () use ($me) {
            require_once SW_PATH . DIRECTORY_SEPARATOR . 'autoload.php';

            $kernel = new \Shopware\Kernel('production', false);
            $kernel->boot();

            $container = $kernel->getContainer();
            $container->get('models')->generateAttributeModels();

            return $container;
        };

        $container['shopware.theme_installer'] = function ($c) {
            $shopwareContainer = $c['shopware.container'];

            /* @var \Shopware\Components\Theme\Installer $themeInstaller */
            return $shopwareContainer->get('theme_installer');
        };

        $container['shopware.update.cleanup'] = function ($container) use ($backupDir) {
            return new Cleanup(SW_PATH, $backupDir);
        };

        $container['shopware.update.chmod'] = function ($container) {
            return new FilePermissionChanger([
                ['chmod' => 0775, 'filePath' => SW_PATH . '/bin/console'],
                ['chmod' => 0775, 'filePath' => SW_PATH . '/var/cache/clear_cache.sh'],
            ]);
        };
    }
}
