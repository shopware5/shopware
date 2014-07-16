<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Recovery\Common\Dump;
use Shopware\Recovery\Update\Controller\BatchController;
use Shopware\Recovery\Update\FilesystemFactory;
use Shopware\Recovery\Update\PathBuilder;
use Shopware\Recovery\Update\Utils;

class Container extends BaseContainer
{
    /**
     * @param \Pimple $pimple
     */
    public function setup(\Pimple $pimple)
    {
        $me = $this;

        $pimple['db'] = function () use ($me) {
            $conn = Utils::getConnection(SW_PATH);

            return $conn;
        };

        $pimple['filesystem.factory'] = function () use ($me) {
            $updateConfig = $me->getParameter('update.config');
            $ftp = (isset($updateConfig['ftp_credentials'])) ? $updateConfig['ftp_credentials'] : array();

            return new FilesystemFactory(SW_PATH, $ftp);
        };

        $pimple['path.builder'] = function () use ($me) {
            $baseDir   = SW_PATH;
            $updateDir = UPDATE_FILES_PATH;
            $backupDir = SW_PATH . '/files/backup';

            return new PathBuilder($baseDir, $updateDir, $backupDir);
        };

        $pimple['migration.manager'] = function () use ($me) {
            $migrationPath = UPDATE_ASSET_PATH . '/migrations/';
            $db = $me->get('db');

            $migrationManger = new MigrationManager($db, $migrationPath);

            return $migrationManger;
        };

        $pimple['dump'] = function () use ($me) {
            $snippetsSql = UPDATE_ASSET_PATH . '/snippets.sql';
            $snippetsSql = file_exists($snippetsSql) ? $snippetsSql :null;

            if (!$snippetsSql) {
                return null;
            }

            return new Dump($snippetsSql);
        };

        $pimple['app'] = function () use ($me) {
            $slimOptions = $me->getParameter('slim');
            $slim = new \Slim\Slim($slimOptions);

            $me->set('slim.request', $slim->request());
            $me->set('slim.response', $slim->response());

            return $slim;
        };

       $pimple['controller.batch'] = function () use ($me) {
            return new BatchController(
                $me->get('slim.request'),
                $me->get('slim.response'),
                $me
            );
        };
    }
}
