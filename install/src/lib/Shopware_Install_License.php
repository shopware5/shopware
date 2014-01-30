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

/**
 * Shopware Check System
 *
 * <code>
 * $list = new Shopware_Components_Check_System();
 * $data = $list->toArray();
 * </code>
 */
class Shopware_Install_License
{
    protected $pluginInstallationQueries = '

    ';

    protected $pluginDownloadPath = 'http://store.shopware.de/downloads/get_license_plugin/shopwareVersion/4000';
    protected $apiGateway = 'http://store.shopware.de/downloads/check_license';

    /**
     *
     * @var PDO
     */
    protected $database;

    protected $error;

    public function evaluateLicense($licenseKey, $host, $edition)
    {
        if (!function_exists("curl_init")) {
            $this->setError("Curl library not found");
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiGateway());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('license' => $licenseKey, 'host' => $host, 'product' => $edition));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);

        if ($response->success != true) {
            if ($response->error == "INVALID") {
                $this->setError("License key seems to be incorrect");
            } elseif ($response->error == "PRODUCT") {
                $this->setError("License key is formally correct but does not match to the selected shopware edition");
            } elseif ($response->error == "HOST") {
                $this->setError("License key is not valid for domain " . $host);
            } else {
                $this->setError("License key seems to be incorrect");
            }
            return false;
        }
        // Insert license in database
        $label = $response->info->label; // Shopware Enterprise Cluster
        $module = $response->info->module; // SwagCommercial
        $product = $response->info->product; // EC
        $host = $response->info->host; // sth.test.shopware.in
        $type = $response->info->type; // 1
        $license = $response->info->license; // ... License-Key...
        $this->installLicensePlugin();
        $this->insertLicenseInDatabase($label, $module, $product, $host, $type, $license);

        $this->downloadLicensePlugin();
        return true;
    }

    public function insertLicenseInDatabase($label, $module, $product, $host, $type, $license)
    {
        try {
            // Delete previous inserted licenses
            $sql = "DELETE FROM s_core_licenses WHERE module = 'SwagCommercial'";
            $this->getDatabase()->query($sql);

            // Insert new license
            $sql = "
            INSERT INTO s_core_licenses (module,host,label,license,version,type,source,added,creation,expiration,active)
            VALUES (?,?,?,?,'1.0.0',1,0,now(),now(),NULL,1)
            ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(array(
                $module, $host, $label, $license
            ));
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;
    }

    public function downloadLicensePlugin()
    {
        $name = 'plugin' . md5($this->getPluginDownloadPath()) . '.zip';
        $tmp = __DIR__ . '/../../../files/downloads/';
        $targetDir = dirname(dirname(dirname(__DIR__))) . "/engine/Shopware/Plugins/Community/";

        if (!file_exists($targetDir) || !is_writable($targetDir)) {
            $this->setError("Directory $targetDir does not exists or is not writable");
            return false;
        }

        if (!file_exists($tmp) || !is_writable($tmp)) {
            $this->setError("Directory $tmp does not exists or is not writable");
            return false;
        }

        // Download file
        try {
            $ch = curl_init($this->pluginDownloadPath);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            $data = curl_exec($ch);
            if (empty($data)) {
                throw Exception("Curl error: " . curl_errno($ch) . "\n" . curl_error($ch));
            }
            file_put_contents($tmp . $name, $data);
            curl_close($ch);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        try {
            if (!class_exists("ZipArchive")) {
                throw new Exception("Can not found ZipArchive extension");
            }
            $zip = new ZipArchive();
            if (!$zip) {
                throw new Exception("Can not create ZipArchive object");
            }
            $zip->open($tmp . $name);
            $zip->extractTo($targetDir);
            $zip->close();
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        if (!file_exists($targetDir . "Core/SwagLicense/Bootstrap.php")) {
            $this->setError("SwagLicense was not extracted correctly. Please install the plugin manually through shopware plugin-manager after installation.");
            return false;
        }

        // Delete file
        @unlink($tmp . $name);

        return true;
    }

    public function installLicensePlugin()
    {
        try {
            $sql = "
            SELECT id FROM s_core_plugins WHERE name = 'SwagLicense'
            ";
            $alreadyExists = $this->getDatabase()->query($sql)->fetchColumn();

            if (!empty($alreadyExists)) {
                $sql = "DELETE FROM s_core_plugins WHERE id = $alreadyExists";
                $this->getDatabase()->query($sql);

                $sql = "DELETE FROM s_core_subscribes WHERE pluginID = $alreadyExists";
                $this->getDatabase()->query($sql);
            }

            $sql = "
            INSERT INTO s_core_plugins (namespace,name,label,source,active,added,installation_date,update_date,refresh_date,author,copyright,version,capability_update,capability_install,capability_enable)
            VALUES (?,?,?,?,?,now(),now(),now(),now(),?,?,?,?,?,?)
            ";
            $array = array(
                'Core', //Namespace,
                'SwagLicense', // name
                'Lizenz-Manager', // label
                'Community', // source
                1, // active
                'shopware AG', // author,
                'Copyright © 2012, shopware AG', //copyright
                '1.0.2', // version
                1, // capability_update
                1, // capability_install
                1 // capability_enable
            );
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute($array);
            $pluginId = $this->getDatabase()->lastInsertId();

            if (empty($pluginId)) {
                throw new Exception("SwagLicense could not be installed in database");
            }

            $sql = "
                INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
                (NULL, 92, 'license', 'Lizenz-Manager', NULL, 0, 0, ?);
            ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $prepareStatement->execute(array(
                $pluginId
            ));

        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Create events
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_License',
            'onInitResourceLicense', $pluginId
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Index',
            'onPostDispatchBackendIndex', $pluginId
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onDispatchLoopStartup', $pluginId
        );
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onPostDispatchBackendConfig', $pluginId
        );
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_License',
            'onGetControllerPathBackend', $pluginId
        );
    }

    protected function subscribeEvent($event, $listener, $pluginId)
    {
        // Insert events in s_core_subscribes
        try {
            $sql = "
            INSERT IGNORE INTO s_core_subscribes (subscribe,listener,pluginID)
            VALUES (?,?,?)
            ";
            $prepareStatement = $this->getDatabase()->prepare($sql);
            $array = array($event, $listener, $pluginId);
            $prepareStatement->execute($array);
        } catch (PDOException $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return true;
    }


    public function setApiGateway($apiGateway)
    {
        $this->apiGateway = $apiGateway;
    }

    public function getApiGateway()
    {
        return $this->apiGateway;
    }

    /**
     * @param \PDO $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return \PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    public function setPluginDownloadPath($pluginDownloadPath)
    {
        $this->pluginDownloadPath = $pluginDownloadPath;
    }

    public function getPluginDownloadPath()
    {
        return $this->pluginDownloadPath;
    }

    public function setPluginInstallationQueries($pluginInstallationQueries)
    {
        $this->pluginInstallationQueries = $pluginInstallationQueries;
    }

    public function getPluginInstallationQueries()
    {
        return $this->pluginInstallationQueries;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}
