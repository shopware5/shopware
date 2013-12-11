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

use Shopware\Models\Shop\Shop;

/**
 * Shopware Plugin Namespace
 *
 * @category  Shopware
 * @package   Shopware\Components\Plugin\Namespace
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
    protected $configStorage = array();

    /**
     * @return Enlight_Config
     */
    protected function initStorage()
    {
        $sql = "
            SELECT
              name, id, name, label,
              description, source, active,
              installation_date as installationDate,
              update_date as updateDate, changes,
              version
            FROM s_core_plugins
            WHERE namespace=?
        ";
        $plugins = $this->Application()->Db()->fetchAssoc($sql, array($this->name));

        foreach ($plugins as $pluginName => $plugin) {
            $plugins[$pluginName]['class'] = $this->buildClassName($this->name, $pluginName);
            $plugins[$pluginName]['path'] = $this->buildPath($this->name, $pluginName, $plugin['source']);
            $plugins[$pluginName]['config'] = array();
        }

        $listeners = $this->loadListeners($this->name);

        return new Enlight_Config(array(
            'plugins'   => $plugins,
            'listeners' => $listeners
        ), true);
    }

    /**
     * @param string $namespace
     * @param string $pluginName
     * @return string
     */
    protected function buildClassName($namespace, $pluginName)
    {
        return implode('_', array(
            'Shopware', 'Plugins', $namespace, $pluginName, 'Bootstrap'
        ));
    }

    /**
     * @param string $namespace
     * @param string $pluginName
     * @param string $pluginSource
     * @return string
     */
    protected function buildPath($namespace, $pluginName, $pluginSource)
    {
        return $this->Application()->AppPath(implode('_', array(
            'Plugins', $pluginSource, $namespace, $pluginName
        )));
    }

    /**
     * @param string $namespace
     * @return array
     */
    protected function loadListeners($namespace)
    {
        $sql = "
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
        ";
        $listeners = $this->Application()->Db()->fetchAll($sql, array($namespace));

        foreach ($listeners as $listenerKey => $listener) {
            if (($position = strpos($listener['listener'], '::')) !== false) {
                $listeners[$listenerKey]['listener'] = substr($listener['listener'], $position + 2);
            }
        }

        return $listeners;
    }

    /**
     * Returns the plugin configuration by the plugin name. If the
     * plugin has no config, the config is automatically set to an empty array.
     *
     * @param   string $name
     * @param   Shop   $shop
     * @return  Enlight_Config|array
     */
    public function getConfig($name, Shop $shop = null)
    {
        if ($shop) {
            $cacheKey = $name . $shop->getId();
        } else {
            $cacheKey  = $name;
            $shop = $this->shop;
        }

        if (!isset($this->configStorage[$cacheKey])) {
            $sql = "
                SELECT
                  ce.name,
                  IFNULL(IFNULL(cv.value, cm.value), ce.value) as value
                FROM s_core_plugins p
                JOIN s_core_config_forms cf
                ON cf.plugin_id = p.id
                JOIN s_core_config_elements ce
                ON ce.form_id = cf.id
                LEFT JOIN s_core_config_values cv
                ON cv.element_id = ce.id
                AND cv.shop_id = ?
                LEFT JOIN s_core_config_values cm
                ON cm.element_id = ce.id
                AND cm.shop_id = ?
                WHERE p.name=?
            ";
            $config = $this->Application()->Db()->fetchPairs($sql, array(
                $shop !== null ? $shop->getId() : null,
                $shop !== null && $shop->getMain() !== null ? $shop->getMain()->getId() : 1,
                $name
            ));
            foreach ($config as $key => $value) {
                $config[$key] = unserialize($value);
            }

            $this->configStorage[$cacheKey] = new Enlight_Config($config, true);
        }

        return $this->configStorage[$cacheKey];
    }

    /**
     * Returns plugin info
     *
     * @param string $plugin
     * @param string $name
     * @return mixed
     */
    protected function getInfo($plugin, $name = null)
    {
        if (!isset($this->storage->plugins->$plugin)) {
            return null;
        }

        if ($name !== null) {
            return $this->storage->plugins->$plugin->$name;
        } else {
            return $this->storage->plugins->$plugin;
        }
    }

    /**
     * Returns plugin source
     *
     * @param string $plugin
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
     * @return int
     */
    public function getPluginId($plugin)
    {
        return $this->getInfo($plugin, 'id');
    }

    /**
     * Set shop instance
     *
     * @param Shop $shop
     * @return Shopware_Components_Plugin_Namespace
     */
    public function setShop(Shop $shop)
    {
        // reset config storage
        $this->configStorage = array();

        $this->shop = $shop;

        return $this;
    }

    /**
     * @param $name
     * @param $config
     * @return \Shopware_Components_Plugin_Bootstrap
     */
    public function initPlugin($name, $config)
    {
        $class = 'Shopware_Plugins_' . $this->name . '_' . $name . '_Bootstrap';
        /** @var $plugin Shopware_Components_Plugin_Bootstrap */
        $plugin = new $class($name, $config);
        return $plugin;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @param \Enlight_Plugin_Bootstrap|\Shopware_Components_Plugin_Bootstrap $plugin
     * @return \Enlight_Plugin_PluginManager|\Shopware_Components_Plugin_Namespace
     */
    public function registerPlugin(Enlight_Plugin_Bootstrap $plugin)
    {
        parent::registerPlugin($plugin);

        $info         = $plugin->Info();
        $capabilities = $plugin->getCapabilities();
        $id           = $this->getPluginId($plugin->getName());

        // normalize autor -> author
        if (isset($info['autor'])) {
            $info['author'] = $info['autor'];
        }

        $data = array(
            'namespace' => $this->getName(),
            'name' => $plugin->getName(),
            'label' => isset($info['label']) ? $info['label'] : $plugin->getName(),
            'version' => isset($info['version']) ? $info['version'] : '1.0.0',
            'author' => isset($info['author']) ? $info['author'] : 'shopware AG',
            'copyright' => isset($info['copyright']) ? $info['copyright'] : 'Copyright © 2012, shopware AG',
            'description' => isset($info['description']) ? $info['description'] : null,
            'license' => isset($info['license']) ? $info['license'] : null,
            'support' => isset($info['support']) ? $info['support'] : null,
            'link' => isset($info['link']) ? $info['link'] : null,
            'changes' => isset($info['changes']) ? $info['changes'] : null,
            'source' => isset($info['source']) ? $info['source'] : 'Default',
            'update_date' => isset($info['updateDate']) ? $info['updateDate'] : null,
            'update_version' => isset($info['updateVersion']) ? $info['updateVersion'] : null,
            'update_source' => isset($info['updateSource']) ? $info['updateSource'] : null,
            'capability_update' => !empty($capabilities['update']),
            'capability_install' => !empty($capabilities['install']),
            'capability_enable' => !empty($capabilities['enable']),
            'capability_dummy' => !empty($capabilities['dummy']),
            'refresh_date' => Zend_Date::now()
        );

        if (empty($id)) {
            $data['added'] = new Zend_Date();
            $this->Application()->Db()->insert('s_core_plugins', $data);
        } else {
            $where = array('id=?' => $id);
            $this->Application()->Db()->update('s_core_plugins', $data, $where);
        }

        $info->merge(new Enlight_Config($data));

        return $this;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @param   Shopware_Components_Plugin_Bootstrap $bootstrap
     * @return  bool
     */
    public function installPlugin(Shopware_Components_Plugin_Bootstrap $bootstrap)
    {
        $this->reloadStorage();

        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->Application()->Models();
        $id = $this->getPluginId($bootstrap->getName());
        $plugin = $em->find('Shopware\Models\Plugin\Plugin', $id);

        $newInfo = $bootstrap->getInfo();
        $newInfo = new Enlight_Config($newInfo, true);
        unset($newInfo->source);
        $bootstrap->Info()->merge($newInfo);
        $this->registerPlugin($bootstrap);

        $this->setConfig($bootstrap->getName(), $bootstrap->Config());

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PreInstall',
            array(
                'subject'  => $this,
                'plugin'   => $bootstrap,
            )
        );

        $result = $bootstrap->install();

        $success = is_bool($result) ? $result : !empty($result['success']);
        if ($success) {
            $this->Application()->Events()->notify(
                'Shopware_Plugin_PostInstall',
                array(
                    'subject'  => $this,
                    'plugin'   => $bootstrap,
                )
            );

            $plugin->setInstalled(new Zend_Date());
            $plugin->setUpdated(new Zend_Date());
            $em->flush($plugin);
            $this->write();

            $em->flush();

            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path().'Snippets/');
            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path().'snippets/');
            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($bootstrap->Path().'Resources/snippet/');

            // Clear proxy cache
            $this->Application()->Hooks()->getProxyFactory()->clearCache();
        }

        return $result;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @param   Shopware_Components_Plugin_Bootstrap $bootstrap
     * @return  bool
     */
    public function uninstallPlugin(Shopware_Components_Plugin_Bootstrap $bootstrap)
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = $this->Application()->Models();

        /** @var \Enlight_Components_Db_Adapter_Pdo_Mysql $db */
        $db = $this->Application()->Db();

        $id = $this->getPluginId($bootstrap->getName());
        $plugin = $em->find('Shopware\Models\Plugin\Plugin', $id);

        $this->Application()->Events()->notify(
            'Shopware_Plugin_PreUninstall',
            array(
                'subject'  => $this,
                'plugin'   => $bootstrap,
            )
        );

        $result = $bootstrap->disable();
        $success = is_bool($result) ? $result : !empty($result['success']);
        if ($success) {
            $result = $bootstrap->uninstall();

            $this->Application()->Events()->notify(
                'Shopware_Plugin_PostUninstall',
                array(
                    'subject'     => $this,
                    'plugin'      => $bootstrap,
                )
            );

            $success = is_bool($result) ? $result : !empty($result['success']);
        }

        if ($success) {
            $plugin->setInstalled(null);
            $plugin->setActive(false);
            $em->flush($plugin);

            // Remove event subscribers
            $sql = 'DELETE FROM `s_core_subscribes` WHERE `pluginID`=?';
            $db->query($sql, array($id));

            // Remove crontab-entries
            $sql = 'DELETE FROM `s_crontab` WHERE `pluginID`=?';
            $db->query($sql, array($id));

            // Remove form
            if ($bootstrap->hasForm()) {
                $form = $bootstrap->Form();
                if ($form->getId()) {
                    $em->remove($form);
                } else {
                    $em->detach($form);
                }
                $em->flush();
            }

            // Remove menu-entry
            $query = 'DELETE FROM Shopware\Models\Menu\Menu m WHERE m.pluginId = ?0';
            $query = $em->createQuery($query);
            $query->execute(array($id));

            // Remove templates
            $query = 'DELETE FROM Shopware\Models\Shop\Template t WHERE t.pluginId = ?0';
            $query = $em->createQuery($query);
            $query->execute(array($id));

            // Remove emotion-components
            $sql = "DELETE s_emotion_element
                    FROM s_emotion_element
                    INNER JOIN s_library_component
                        ON s_library_component.id = s_emotion_element.componentID
                        AND s_library_component.pluginID = :pluginId";

            $db->query($sql, array(':pluginId' => $id));

            $sql = "DELETE s_library_component_field, s_library_component
                    FROM s_library_component_field
                    INNER JOIN s_library_component
                        ON s_library_component.id = s_library_component_field.componentID
                        AND s_library_component.pluginID = :pluginId";

            $db->query($sql, array(':pluginId' => $id));
        }

        return $result;
    }

    /**
     * Registers a plugin in the collection.
     *
     * @param   Shopware_Components_Plugin_Bootstrap $plugin
     * @return  bool
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
            array(
                'subject'  => $this,
                'plugin'   => $plugin,
            )
        );

        $result = $plugin->update($oldVersion);
        $success = is_bool($result) ? $result : !empty($result['success']);
        if ($success) {
            $this->Application()->Events()->notify(
                'Shopware_Plugin_PostUpdate',
                array(
                    'subject'  => $this,
                    'plugin'   => $plugin,
                )
            );

            $newInfo->set('updateVersion', null);
            $newInfo->set('updateSource', null);
            $newInfo->set('updateDate', Zend_Date::now());
            $plugin->Info()->merge($newInfo);
            $this->registerPlugin($plugin);

            // Save events / Hooks
            $this->write();

            $form = $plugin->Form();
            if ($form->hasElements()) {
                $this->Application()->Models()->persist($form);
            }
            $this->Application()->Models()->flush();

            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path().'Snippets/');
            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path().'snippets/');
            Shopware()->ResourceLoader()->get('shopware.snippet_database_handler')->loadToDatabase($plugin->Path().'Resources/snippet/');


            // Clear proxy cache
            $this->Application()->Hooks()->getProxyFactory()->clearCache();
        }

        return $result;
    }

    /**
     * Writes all registered plugins into the storage.
     * The subscriber and the registered plugins are converted to an array.
     *
     * @return  Enlight_Plugin_Namespace_Config
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

            $this->Application()->Db()->query($sql, array(
                $subscribe['name'],
                0,
                $subscribe['listener'],
                $subscribe['position'],
                $subscribe['pluginID'],
            ));
        }

        return $this;
    }
}
