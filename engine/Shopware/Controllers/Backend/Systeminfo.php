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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\OptimizerService;
use Shopware\Components\CSRFWhitelistAware;

/**
 * This controller reads out all necessary configs.
 */
class Shopware_Controllers_Backend_Systeminfo extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    public function initAcl()
    {
        $this->addAclPermission('getConfigList', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('getPathList', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('getFileList', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('getVersionList', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('getEnconder', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('getOptimizers', 'read', "You're not allowed to open the module.");
        $this->addAclPermission('info', 'read', "You're not allowed to open the module.");
    }

    /**
     * Disable template engine for all actions
     */
    public function preDispatch()
    {
        if (!\in_array($this->Request()->getActionName(), ['index', 'load', 'info'])) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * Function to get all system-configs and its requirements
     * The array also contains a status, whether the minimum requirements are met
     * The encoder-configs are excluded, because they are needed in another store
     * getEncoderAction loads those two encoder-configs
     *
     * @return void
     */
    public function getConfigListAction()
    {
        $result = $this->get('shopware.requirements')->toArray();

        foreach ($result['checks'] as $key => $config) {
            // Those configs mustn't be displayed in the grid
            if ($config['name'] === 'mod_rewrite') {
                unset($result['checks'][$key]);
            }
        }
        $this->View()->assign(['success' => true, 'data' => array_merge($result['checks'])]);
    }

    /**
     * Function to get all necessary paths and a status, whether the paths are available
     *
     * @return void
     */
    public function getPathListAction()
    {
        $list = new Shopware_Components_Check_Path();
        $this->View()->assign(['success' => true, 'data' => $list->toArray()]);
    }

    /**
     * Function to get all necessary files and the status, whether those files match with the original Shopware-Files
     *
     * @return void
     */
    public function getFileListAction()
    {
        $fileName = __DIR__ . '/../../Components/Check/Data/Files.md5sums';
        if (!is_file($fileName)) {
            $this->View()->assign(['success' => true, 'data' => []]);

            return;
        }

        // skip files from check
        $skipList = [
        ];

        $list = new Shopware_Components_Check_File(
            $fileName,
            $this->container->getParameter('shopware.app.rootDir'),
            $skipList
        );

        $this->View()->assign(['success' => true, 'data' => $list->toArray()]);
    }

    /**
     * Function to get all plugins and its version.
     *
     * @return void
     */
    public function getVersionListAction()
    {
        $rows = $this->get(Connection::class)->createQueryBuilder()
            ->select(['version', 'name', 'namespace', 'source'])
            ->from('s_core_plugins')
            ->execute()
            ->fetchAllAssociative();

        foreach ($rows as $key => $row) {
            $rows[$key]['name'] = $row['namespace'] . '/' . $row['source'] . '/' . $row['name'];
        }

        array_unshift($rows, ['name' => 'Shopware', 'version' => $this->get('config')->get('Version')]);

        $this->View()->assign(['success' => true, 'data' => $rows]);
    }

    /**
     * Function to get timezone diff
     *
     * @return void
     */
    public function getTimezoneAction()
    {
        $connection = $this->get(Connection::class);
        $offset = 0;
        $timeZone = '';
        try {
            $timeZone = $connection->executeQuery('SELECT @@SESSION.time_zone')->fetchOne();

            if ($timeZone === 'SYSTEM') {
                $timeZone = $connection->executeQuery('SELECT @@system_time_zone')->fetchOne();
            }
        } catch (PDOException $e) {
        }

        if (!empty($timeZone)) {
            $databaseZone = null;
            if (\in_array($timeZone[0], ['-', '+'], true)) {
                $databaseZone = new DateTimeZone($timeZone);
            } else {
                $timeZoneFromAbbr = timezone_name_from_abbr($timeZone);
                if (\is_string($timeZoneFromAbbr)) {
                    $databaseZone = timezone_open($timeZoneFromAbbr);
                    if (!$databaseZone instanceof DateTimeZone) {
                        $databaseZone = null;
                    }
                }
            }

            $phpZone = timezone_open(date_default_timezone_get());
            if ($databaseZone instanceof DateTimeZone && $phpZone instanceof DateTimeZone) {
                $databaseTime = new DateTime('now', $databaseZone);
                $offset = abs($databaseZone->getOffset(new DateTime()) - $phpZone->getOffset($databaseTime));
            }
        }

        if (empty($offset)) {
            $sql = 'SELECT UNIX_TIMESTAMP()-' . time();
            $offset = (int) $connection->executeQuery($sql)->fetchOne();
        }

        $this->View()->assign(['success' => true, 'offset' => $offset < 60 ? 0 : round($offset / 60)]);
    }

    /**
     * Function to display the phpinfo
     *
     * @return void
     */
    public function infoAction()
    {
        $this->get('front')->Plugins()->ViewRenderer()->setNoRender();
        $_COOKIE = [];
        $_REQUEST = [];
        $_SERVER['HTTP_COOKIE'] = null;
        if (\function_exists('apache_setenv')) {
            apache_setenv('HTTP_COOKIE', '');
        }
        phpinfo();
    }

    /**
     * @return void
     */
    public function getOptimizersAction()
    {
        $optimizers = $this->get(OptimizerService::class)->getOptimizers();
        $optimizerResult = [];

        foreach ($optimizers as $optimizer) {
            $optimizerResult[] = [
                'name' => $optimizer->getName(),
                'mimeTypes' => $optimizer->getSupportedMimeTypes(),
                'runnable' => $optimizer->isRunnable(),
            ];
        }

        $this->View()->assign('success', true);
        $this->View()->assign('data', $optimizerResult);
        $this->View()->assign('total', \count($optimizerResult));
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'info',
        ];
    }
}
