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

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

/**
 * Shopware Plugin Namespace
 */
class Shopware_Components_Plugin_Namespace extends Enlight_Plugin_Namespace_Config
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @param string              $name
     * @param Enlight_Config|null $storage
     */
    public function __construct($name, $storage, array $pluginDirectories, ConfigReader $configReader)
    {
        $this->pluginDirectories = $pluginDirectories;
        $this->configReader = $configReader;

        parent::__construct($name, $storage);
    }

    /**
     * Returns the plugin configuration by the plugin name. If the
     * plugin has no config, the config is automatically set to an empty array.
     *
     * @param string $name
     * @param Shop   $shop
     *
     * @return Enlight_Config
     */
    public function getConfig($name, Shop $shop = null)
    {
        if (!$shop) {
            $shop = $this->shop;
        }

        $config = $this->configReader->getByPluginName($name, $shop);

        return new Enlight_Config($config, true);
    }

    /**
     * Returns plugin source
     *
     * @param string $plugin
     *
     * @return string
     */
    public function getSource($plugin)
    {
        return $this->getInfo($plugin, 'source');
    }

    /**
     * Returns plugin id
     *
     * @param string $plugin
     *
     * @return int
     */
    public function getPluginId($plugin)
    {
        return $this->getInfo($plugin, 'id');
    }

    /**
     * Set shop instance
     *
     * @return Shopware_Components_Plugin_Namespace
     */
    public function setShop(Shop $shop)
    {
        $this->shop = $shop;

        return $this;
    }

    /**
     * This is used to install a new plugin
     *
     * @param string         $name
     * @param Enlight_Config $config
     *
     * @return \Shopware_Components_Plugin_Bootstrap
     */
    public function initPlugin($name, $config)
    {
        $class = 'Shopware_Plugins_' . $this->name . '_' . $name . '_Bootstrap';

        return new $class($name, $config);
    }

    /**
     * Registers a plugin in the collection.
     *
     * @param \Enlight_Plugin_Bootstrap|\Shopware_Components_Plugin_Bootstrap $plugin
     * @param DateTimeInterface                                               $refreshDate
     *
     * @return Enlight_Plugin_PluginManager|Shopware_Components_Plugin_Namespace
     */
    public function registerPlugin(Enlight_Plugin_Bootstrap $plugin, \DateTimeInterface $refreshDate = null)
    {
        parent::registerPlugin($plugin);

        if ($refreshDate === null) {
            $refreshDate = new \DateTimeImmutable();
        }

        $info = $plugin->Info();
        $capabilities = $plugin->getCapabilities();
        $id = $this->getPluginId($plugin->getName());

        // normalize autor -> author
        if (isset($info['autor'])) {
            $info['author'] = $info['autor'];
        }

        $data = [
            'namespace' => $this->getName(),
            'name' => $plugin->getName(),
            'label' => isset($info['label']) && is_string($info['label']) ? $info['label'] : $plugin->getName(),
            'version' => isset($info['version']) ? $info['version'] : '1.0.0',
            'author' => isset($info['author']) ? $info['author'] : 'shopware AG',
            'copyright' => isset($info['copyright']) ? $info['copyright'] : 'Copyright © 2012, shopware AG',
            'description' => isset($info['description']) ? $info['description'] : null,
            'license' => isset($info['license']) ? $info['license'] : null,
            'support' => isset($info['support']) ? $info['support'] : null,
            'link' => isset($info['link']) ? $info['link'] : null,
            'source' => isset($info['source']) ? $info['source'] : 'Default',
            'update_date' => isset($info['updateDate']) ? $info['updateDate'] : null,
            'update_version' => isset($info['updateVersion']) ? $info['updateVersion'] : null,
            'update_source' => isset($info['updateSource']) ? $info['updateSource'] : null,
            'capability_update' => !empty($capabilities['update']),
            'capability_install' => !empty($capabilities['install']),
            'capability_enable' => !empty($capabilities['enable']),
            'capability_secure_uninstall' => !empty($capabilities['secureUninstall']),
            'refresh_date' => $refreshDate,
        ];

        $connection = $this->Application()->Container()->get('dbal_connection');
        if (empty($id)) {
            $data['added'] = $refreshDate;
            $connection->insert(
                's_core_plugins',
                $data,
                [
                    'added' => 'datetime',
                    'refresh_date' => 'datetime',
                    'update_date' => 'datetime',
                ]
            );
        } else {
            $connection->update(
                's_core_plugins',
                $data,
                ['id' => $id],
                [
                    'refresh_date' => 'datetime',
                    'update_date' => 'datetime',
                ]
            );
        }

        $info->merge(new Enlight_Config($data));

        return $this;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @return bool|array
     */
    public function installPlugin(Shopware_Components_Plugin_Bootstrap $bootstrap)
    {
        $this->reloadStorage();

        /** @var ModelManager $em */
        $em = $this->Application()->Models();
        $id = $this->getPluginId($bootstrap->getName());
        $plugin = $em->find(Plugin::class, $id);

        $newInfo = $bootstrap->getInfo();
        $newInfo = new Enlight_Config($newInfo, true);
        unset($newInfo->source);
        $bootstrap->Info()->merge($newInfo);
        $this->registerPlugin($bootstrap);

        $this->setConfig($bootstrap->getName(), $bootstrap->Config());

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PreInstall',
            [
                'subject' => $this,
                'plugin' => $bootstrap,
            ]
        );

        $result = $bootstrap->install();

        $success = is_bool($result) ? $result : !empty($result['success']);
        if ($success) {
            $this->Application()->Events()->notify(
                'Shopware_Plugin_PostInstall',
                [
                    'subject' => $this,
                    'plugin' => $bootstrap,
                ]
            );

            $plugin->setInstalled(new DateTime());
            $plugin->setUpdated(new DateTime());
            $em->flush($plugin);
            $this->write();

            $em->flush();

            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path() . 'Snippets/');
            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path() . 'snippets/');
            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path() . 'Resources/snippet/');

            // Clear proxy cache
            $this->Application()->Hooks()->getProxyFactory()->clearCache();
        }

        $db = $this->Application()->Container()->get('db');

        $resourceId = $db->fetchOne(
            "SELECT id FROM s_core_acl_resources WHERE name = 'widgets'"
        );

        if (!$resourceId) {
            return $result;
        }

        /** @var Shopware\Models\Plugin\Plugin $plugin */
        /** @var Shopware\Models\Widget\Widget $widget */
        foreach ($plugin->getWidgets() as $widget) {
            $name = $widget->getName();
            $db->insert('s_core_acl_privileges', [
                'name' => $name,
                'resourceID' => $resourceId,
            ]);
        }

        return $result;
    }

    /**
     * Registers a plugin in the collection.
     * If $removeData is set to false the plugin data will not be removed.
     *
     * @param bool $removeData
     *
     * @throws Exception
     *
     * @return bool|array
     */
    public function uninstallPlugin(Shopware_Components_Plugin_Bootstrap $bootstrap, $removeData = true)
    {
        /** @var ModelManager $em */
        $em = $this->Application()->Models();

        $connection = $em->getConnection();

        $id = $this->getPluginId($bootstrap->getName());
        $plugin = $em->find(\Shopware\Models\Plugin\Plugin::class, $id);

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PreUninstall',
            [
                'subject' => $this,
                'plugin' => $bootstrap,
                'removeData' => $removeData,
            ]
        );

        $result = $bootstrap->disable();
        $capabilities = $bootstrap->getCapabilities();
        $capabilities['secureUninstall'] = !empty($capabilities['secureUninstall']);
        $success = is_bool($result) ? $result : !empty($result['success']);

        if (!$success) {
            return $result;
        }

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PostUninstall',
            [
                'subject' => $this,
                'plugin' => $bootstrap,
                'removeData' => $removeData,
            ]
        );

        if ($removeData) {
            $result = $bootstrap->uninstall();
        } elseif ($capabilities['secureUninstall']) {
            $result = $bootstrap->secureUninstall();
        } else {
            throw new \Exception('Plugin does not support secure uninstall.');
        }

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PostUninstall',
            [
                'subject' => $this,
                'plugin' => $bootstrap,
                'removeData' => $removeData,
            ]
        );

        $success = is_bool($result) ? $result : !empty($result['success']);

        if (!$success) {
            return $result;
        }

        $plugin->setInstalled(null);
        $plugin->setActive(false);
        $em->flush($plugin);

        // Remove event subscribers
        $sql = 'DELETE FROM s_core_subscribes WHERE pluginID = ?';
        $connection->executeUpdate($sql, [$id]);

        // Remove crontab-entries
        $sql = 'DELETE FROM s_crontab WHERE pluginID = ?';
        $connection->executeUpdate($sql, [$id]);

        // Remove form
        $this->removeForm($bootstrap, $removeData);

        // Remove snippets
        if ($capabilities['secureUninstall']) {
            $bootstrap->removeSnippets($removeData);
        } else {
            $bootstrap->removeSnippets(true);
        }

        // Remove menu-entries
        $sql = 'DELETE FROM s_core_menu WHERE pluginID = ?';
        $connection->executeUpdate($sql, [$id]);

        // Remove templates
        $sql = 'DELETE FROM s_core_templates WHERE plugin_id = ?';
        $connection->executeUpdate($sql, [$id]);

        // Remove emotion-components
        $sql = 'DELETE s_emotion_element_value, s_emotion_element
                FROM s_emotion_element_value
                RIGHT JOIN s_emotion_element
                    ON s_emotion_element.id = s_emotion_element_value.elementID
                INNER JOIN s_library_component
                    ON s_library_component.id = s_emotion_element.componentID
                    AND s_library_component.pluginID = :pluginId';

        $connection->executeUpdate($sql, [':pluginId' => $id]);

        $sql = 'DELETE s_library_component_field, s_library_component
                FROM s_library_component_field
                INNER JOIN s_library_component
                    ON s_library_component.id = s_library_component_field.componentID
                    AND s_library_component.pluginID = :pluginId';

        $connection->executeUpdate($sql, [':pluginId' => $id]);

        $this->removePluginWidgets($id);

        return $result;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @throws \Exception
     * @throws \Enlight_Config_Exception
     * @throws \Enlight_Event_Exception
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     *
     * @return bool|array
     */
    public function updatePlugin(Shopware_Components_Plugin_Bootstrap $plugin)
    {
        $this->reloadStorage();

        $name = $plugin->getName();
        $oldVersion = $this->getInfo($name, 'version');
        $newInfo = $plugin->getInfo();
        $newInfo = new Enlight_Config($newInfo, true);
        unset($newInfo->source);

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PreUpdate',
            [
                'subject' => $this,
                'plugin' => $plugin,
            ]
        );

        $result = $plugin->update($oldVersion);
        $success = is_bool($result) ? $result : !empty($result['success']);
        if ($success) {
            $this->Application()->Events()->notify(
                'Shopware_Plugin_PostUpdate',
                [
                    'subject' => $this,
                    'plugin' => $plugin,
                ]
            );

            $newInfo->set('updateVersion', null);
            $newInfo->set('updateSource', null);
            $newInfo->set('updateDate', new DateTimeImmutable());
            $plugin->Info()->merge($newInfo);
            $this->registerPlugin($plugin);

            // Save events / Hooks
            $this->write();

            $form = $plugin->Form();
            if ($form->hasElements()) {
                $this->Application()->Models()->persist($form);
            }
            $this->Application()->Models()->flush();

            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path() . 'Snippets/');
            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path() . 'snippets/');
            $this->Application()->Container()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path() . 'Resources/snippet/');

            // Clear proxy cache
            $this->Application()->Hooks()->getProxyFactory()->clearCache();
        }

        return $result;
    }

    /**
     * Writes all registered plugins into the storage.
     * The subscriber and the registered plugins are converted to an array.
     *
     * @return Enlight_Plugin_Namespace_Config
     */
    public function write()
    {
        $subscribes = $this->Subscriber()->toArray();

        foreach ($subscribes as $subscribe) {
            $subscribe['pluginID'] = $this->getInfo($subscribe['plugin'], 'id');
            if (!isset($subscribe['pluginID'])) {
                continue;
            }
            $subscribe['listener'] = $this->getInfo($subscribe['plugin'], 'class')
                . '::' . $subscribe['listener'];

            $sql = '
                INSERT INTO `s_core_subscribes` (
                    `subscribe`,
                    `type`,
                    `listener`,
                    `position`,
                    `pluginID`
                ) VALUES (
                    ?, ?, ?, ?, ?
                ) ON DUPLICATE KEY UPDATE
                    `position` = VALUES(`position`),
                    `pluginID` = VALUES(`pluginID`)
            ';

            $this->Application()->Db()->query($sql, [
                $subscribe['name'],
                0,
                $subscribe['listener'],
                $subscribe['position'],
                $subscribe['pluginID'],
            ]);
        }

        return $this;
    }

    /**
     * @return Enlight_Config
     */
    protected function initStorage()
    {
        $sql = '
            SELECT
              name, 
              id, 
              name, 
              label,
              description, 
              source, 
              active,
              installation_date as installationDate,
              update_date as updateDate, 
              version
            FROM s_core_plugins
            WHERE namespace=?
        ';

        $connection = $this->Application()->Container()->get('dbal_connection');
        $rows = $connection->fetchAll($sql, [$this->name]);

        $plugins = [];
        foreach ($rows as $row) {
            $pluginName = $row['name'];
            $plugins[$pluginName] = $row;
            $plugins[$pluginName]['class'] = $this->buildClassName($this->name, $pluginName);
            $plugins[$pluginName]['path'] = $this->buildPath($this->name, $pluginName, $row['source']);
            $plugins[$pluginName]['config'] = [];

            if ($plugins[$pluginName]['installationDate']) {
                $plugins[$pluginName]['installationDate'] = new \DateTime($row['installationDate']);
            }
            if ($plugins[$pluginName]['updateDate']) {
                $plugins[$pluginName]['updateDate'] = new \DateTime($row['updateDate']);
            }
        }

        $listeners = $this->loadListeners($this->name);

        return new Enlight_Config([
            'plugins' => $plugins,
            'listeners' => $listeners,
        ], true);
    }

    /**
     * @param string $namespace
     * @param string $pluginName
     *
     * @return string
     */
    protected function buildClassName($namespace, $pluginName)
    {
        return implode('_', [
            'Shopware', 'Plugins', $namespace, $pluginName, 'Bootstrap',
        ]);
    }

    /**
     * @param string $namespace
     * @param string $pluginName
     * @param string $pluginSource
     *
     * @return string
     */
    protected function buildPath($namespace, $pluginName, $pluginSource)
    {
        $baseDir = $this->pluginDirectories[$pluginSource];

        return $baseDir . $namespace . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $namespace
     *
     * @return array
     */
    protected function loadListeners($namespace)
    {
        $sql = '
            SELECT
              ce.subscribe as name,
              ce.listener,
              ce.position,
              cp.name as plugin
             FROM s_core_subscribes ce
             JOIN s_core_plugins cp
             ON cp.id=ce.pluginID
             AND cp.active=1
             AND cp.namespace=?
             WHERE ce.type=0
             ORDER BY name, position
        ';
        $listeners = $this->Application()->Db()->fetchAll($sql, [$namespace]);

        foreach ($listeners as $listenerKey => $listener) {
            if (($position = strpos($listener['listener'], '::')) !== false) {
                $listeners[$listenerKey]['listener'] = substr($listener['listener'], $position + 2);
            }
        }

        return $listeners;
    }

    /**
     * Returns plugin info
     *
     * @param string $plugin
     * @param string $name
     */
    protected function getInfo($plugin, $name = null)
    {
        if (!isset($this->storage->plugins->$plugin)) {
            return null;
        }

        if ($name !== null) {
            return $this->storage->plugins->$plugin->$name;
        }

        return $this->storage->plugins->$plugin;
    }

    /**
     * Helper function which removes all plugin widgets for the backend widget system.
     *
     * The function removes additionally the auto generated acl rules for the widgets.
     *
     * @param int $pluginId
     */
    private function removePluginWidgets($pluginId)
    {
        /** @var Connection $connection */
        $connection = $this->Application()->Container()->get('dbal_connection');

        $sql = "
            DELETE widgets, views, priv
            FROM s_core_widgets widgets
                LEFT JOIN s_core_widget_views views
                    ON views.widget_id = widgets.id
                LEFT JOIN s_core_acl_privileges priv
                    ON priv.name = widgets.name
                LEFT JOIN s_core_acl_resources resource
                    ON resource.name = 'widgets'
                    AND resource.id = priv.resourceID
            WHERE widgets.plugin_id = :pluginId
        ";

        $connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * Helper function to remove a plugins form or only its translations (if removeData == false)
     *
     * @param bool $removeData
     *
     * @throws Exception
     */
    private function removeForm(Shopware_Components_Plugin_Bootstrap $bootstrap, $removeData = true)
    {
        if (!$bootstrap->hasForm()) {
            return;
        }

        if ($removeData) {
            /** @var ModelManager $em */
            $em = $this->Application()->Models();
            $form = $bootstrap->Form();

            if ($form->getId()) {
                $em->remove($form);
            } else {
                $em->detach($form);
            }
            $em->flush();

            return;
        }

        $capabilities = $bootstrap->getCapabilities();

        if ($capabilities['secureUninstall']) {
            /** @var \Enlight_Components_Db_Adapter_Pdo_Mysql $db */
            $db = $this->Application()->Db();
            $id = $this->getPluginId($bootstrap->getName());

            // Remove element translations
            $sql = 'DELETE `s_core_config_element_translations`
                    FROM `s_core_config_element_translations`
                    INNER JOIN `s_core_config_elements`
                       ON `s_core_config_element_translations`.`element_id` = `s_core_config_elements`.`id`
                    INNER JOIN `s_core_config_forms`
                       ON `s_core_config_elements`.`form_id` = `s_core_config_forms`.`id`
                       AND `s_core_config_forms`.`plugin_id` = ?';
            $db->query($sql, [$id]);

            // Remove form translations
            $sql = 'DELETE `s_core_config_form_translations`
                    FROM `s_core_config_form_translations`
                    INNER JOIN `s_core_config_forms`
                       ON `s_core_config_form_translations`.`form_id` = `s_core_config_forms`.`id`
                       AND `s_core_config_forms`.`plugin_id` = ?';
            $db->query($sql, [$id]);

            return;
        }

        throw new \Exception('Plugin does not support secure uninstall.');
    }
}
