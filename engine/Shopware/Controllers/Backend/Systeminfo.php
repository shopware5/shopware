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
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'info'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    /**
     * Function to get all system-configs and its requirements
     * The array also contains a status, whether the minimum requirements are met
     * The encoder-configs are excluded, because they are needed in another store
     * getEncoderAction loads those two encoder-configs
     */
    public function getConfigListAction()
    {
        $result = $this->get('shopware.requirements')->toArray();

        foreach ($result['checks'] as $key => &$config) {
            // Those configs mustn't be displayed in the grid
            if ($config['name'] == 'mod_rewrite') {
                unset($result['checks'][$key]);
            }
        }
        $this->View()->assign(['success' => true, 'data' => array_merge($result['checks'])]);
    }

    /**
     * Function to get all necessary paths and a status, whether the paths are available
     */
    public function getPathListAction()
    {
        $list = new Shopware_Components_Check_Path();
        $this->View()->assign(['success' => true, 'data' => $list->toArray()]);
    }

    /**
     * Function to get all necessary files and the status, whether those files match with the original Shopware-Files
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

        $list = new Shopware_Components_Check_File($fileName, Shopware()->DocPath(), $skipList);

        $this->View()->assign(['success' => true, 'data' => $list->toArray()]);
    }

    /**
     * Function to get all plugins and its version.
     */
    public function getVersionListAction()
    {
        $select = Shopware()->Db()->select()->from(
            's_core_plugins',
            ['version', 'name', 'namespace', 'source']
        );

        $rows = Shopware()->Db()->fetchAll($select);

        foreach ($rows as $key => $row) {
            $rows[$key]['name'] = $row['namespace'] . '/' . $row['source'] . '/' . $row['name'];
        }

        array_unshift($rows, ['name' => 'Shopware', 'version' => Shopware()->Config()->Version]);

        $this->View()->assign(['success' => true, 'data' => $rows]);
    }

    /**
     * Function to get timezone diff
     */
    public function getTimezoneAction()
    {
        $offset = 0;
        try {
            $sql = <<<'SQL'
                SELECT timeZones.timeZone
                FROM (
                    SELECT @@SESSION.time_zone AS timeZone
                    UNION
                    SELECT @@system_time_zone AS timeZone
                ) AS timeZones
                WHERE timeZone != 'SYSTEM'
                LIMIT 1
SQL;

            $timezone = $this->container->get('dbal_connection')
                ->query($sql)
                ->fetchColumn();

            if (in_array($timezone[0], ['-', '+'], true)) {
                $databaseZone = new DateTimeZone($timezone);
            } else {
                $databaseZone = timezone_open(timezone_name_from_abbr($timezone));
            }

            $phpZone = timezone_open(date_default_timezone_get());
            $databaseTime = new DateTime('now', $databaseZone);

            if (!empty($timezone)) {
                $offset = abs($databaseZone->getOffset(new DateTime()) - $phpZone->getOffset($databaseTime));
            }
        } catch (\PDOException $e) {
        }
        if (empty($offset)) {
            $sql = 'SELECT UNIX_TIMESTAMP()-' . time();
            $offset = $this->container->get('dbal_connection')->query($sql)->fetchColumn();
        }

        $this->View()->assign(['success' => true, 'offset' => $offset < 60 ? 0 : round($offset / 60)]);
    }

    /**
     * Function to display the phpinfo
     */
    public function infoAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $_COOKIE = [];
        $_REQUEST = [];
        $_SERVER['HTTP_COOKIE'] = null;
        if (function_exists('apache_setenv')) {
            apache_setenv('HTTP_COOKIE', '');
        }
        phpinfo();
    }

    public function getOptimizersAction()
    {
        $optimizers = $this->get('shopware_media.optimizer_service')->getOptimizers();
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
        $this->View()->assign('total', count($optimizerResult));
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'info',
        ];
    }
}
