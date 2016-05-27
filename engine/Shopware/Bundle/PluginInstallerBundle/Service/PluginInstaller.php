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

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin as PluginBootstrap;
use Shopware\Components\Plugin\PluginContext;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin;
use Shopware\Components\Plugin\FormSynchronizer;
use Shopware\Components\Plugin\MenuSynchronizer;
use Shopware\Components\Plugin\XmlMenuReader;
use Shopware\Components\Plugin\XmlConfigDefinitionReader;
use Shopware\Components\Plugin\XmlPluginInfoReader;

class PluginInstaller
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DatabaseHandler
     */
    private $snippetHandler;

    /**
     * @param ModelManager $em
     * @param DatabaseHandler $snippetHandler
     */
    public function __construct(ModelManager $em, DatabaseHandler $snippetHandler)
    {
        $this->em = $em;
        $this->connection = $this->em->getConnection();
        $this->snippetHandler = $snippetHandler;
    }

    /**
     * @param Plugin $plugin
     * @return PluginContext
     * @throws \Exception
     */
    public function installPlugin(Plugin $plugin)
    {
        /** @var Kernel $kernel */
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        $context = new PluginContext($plugin, \Shopware::VERSION, $plugin->getVersion());

        $this->em->transactional(function ($em) use ($pluginBootstrap, $plugin, $context) {

            $this->installResources($pluginBootstrap, $plugin);

            $this->em->flush($plugin);

            $pluginBootstrap->install($context);

            $plugin->setInstalled(new \DateTime());
            $plugin->setUpdated(new \DateTime());

            $this->em->flush($plugin);
        });

        return $context;
    }

    /**
     * @param Plugin $plugin
     * @param bool $removeData
     * @return PluginContext
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        $context = new PluginContext($plugin, \Shopware::VERSION, $plugin->getVersion());
        $bootstrap = $this->getPluginByName($plugin->getName());

        $bootstrap->uninstall($context, !$removeData);

        $plugin->setInstalled(null);
        $plugin->setActive(false);

        $this->em->flush($plugin);

        $pluginId = $plugin->getId();

        $this->removeEventSubscribers($pluginId);
        $this->removeCrontabEntries($pluginId);
        $this->removeMenuEntries($pluginId);
        $this->removeTemplates($pluginId);
        $this->removeEmotionComponents($pluginId);

        $this->removeSnippets($bootstrap, $removeData);
        if ($removeData) {
            $this->removeFormsAndElements($pluginId);
        }

        return $context;
    }

    /**
     * @param PluginBootstrap $bootstrap
     * @param boolean $removeDirty
     */
    private function removeSnippets(PluginBootstrap $bootstrap, $removeDirty)
    {
        $this->snippetHandler->removeFromDatabase($bootstrap->getPath() . '/Resources/snippets/', $removeDirty);
    }

    /**
     * @param Plugin $plugin
     * @return PluginContext
     * @throws \Exception
     */
    public function updatePlugin(Plugin $plugin)
    {
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        $context = new PluginContext(
            $plugin,
            \Shopware::VERSION,
            $plugin->getVersion(),
            $plugin->getUpdateVersion()
        );

        $this->em->transactional(function ($em) use ($pluginBootstrap, $plugin, $context) {
            $this->installResources($pluginBootstrap, $plugin);

            $pluginBootstrap->update($context);

            $plugin->setVersion($context->getUpdateVersion());
            $plugin->setUpdateVersion(null);
            $plugin->setUpdateSource(null);
            $plugin->setUpdated(new \DateTime());

            $this->em->flush($plugin);
        });

        return $context;
    }

    /**
     * @param PluginBootstrap $bootstrap
     * @param Plugin $plugin
     */
    private function installResources(PluginBootstrap $bootstrap, Plugin $plugin)
    {
        if (is_file($bootstrap->getPath().'/Resources/config.xml')) {
            $this->installForm($plugin, $bootstrap->getPath().'/Resources/config.xml');
        }

        if (is_file($bootstrap->getPath().'/Resources/menu.xml')) {
            $this->installMenu($plugin, $bootstrap->getPath().'/Resources/menu.xml');
        }

        if (file_exists($bootstrap->getPath() . '/Resources/snippets')) {
            $this->installSnippets($bootstrap);
        }
    }

    /**
     * @param PluginBootstrap $bootstrap
     */
    private function installSnippets(PluginBootstrap $bootstrap)
    {
        $this->snippetHandler->loadToDatabase($bootstrap->getPath() . '/Resources/snippets/');
    }

    /**
     * @param Plugin $plugin
     * @return PluginContext
     */
    public function activatePlugin(Plugin $plugin)
    {
        $context = new PluginContext($plugin, \Shopware::VERSION, $plugin->getVersion());

        $bootstrap = $this->getPluginByName($plugin->getName());
        $bootstrap->activate($context);

        $plugin->setActive(true);
        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @param Plugin $plugin
     * @return PluginContext
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        $context = new PluginContext($plugin, \Shopware::VERSION, $plugin->getVersion());
        $bootstrap = $this->getPluginByName($plugin->getName());
        $bootstrap->deactivate($context);

        $plugin->setActive(false);
        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @param \DateTimeInterface $refreshDate
     */
    public function refreshPluginList(\DateTimeInterface $refreshDate)
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $plugins = $kernel->getPlugins();

        foreach ($plugins as $plugin) {
            $pluginInfoPath = $plugin->getPath().'/plugin.xml';
            if (is_file($pluginInfoPath)) {
                $xmlConfigReader = new XmlPluginInfoReader();
                $info = $xmlConfigReader->read($pluginInfoPath);
            } else {
                $info = [];
            }

            $currentPluginInfo = $this->em->getConnection()->fetchAssoc(
                'SELECT * FROM s_core_plugins WHERE `name` LIKE ?',
                [$plugin->getName()]
            );

            $description = '';
            if (isset($info['description'])) {
                foreach ($info['description'] as $locale => $string) {
                    $description .= sprintf('<div lang="%s">%s</div>', $locale, $string);
                }
            }

            $info['description'] = $description;
            $info['version'] = isset($info['version']) ? $info['version'] : '0.0.1';
            $info['author'] = isset($info['author']) ? $info['author'] : null;
            $info['link'] = isset($info['link']) ? $info['link'] : null;
            $info['label'] = isset($info['label']) && isset($info['label']['en']) ? $info['label']['en'] : $plugin->getName();

            $data = [
                'namespace' => 'ShopwarePlugins',
                'version' => $info['version'],
                'author' => $info['author'],
                'name' => $plugin->getName(),
                'link' => $info['link'],
                'label' => $info['label'],
                'description' => $info['description'],
                'capability_update' => true,
                'capability_install' => true,
                'capability_enable' => true,
                'capability_secure_uninstall' => true,
                'refresh_date' => $refreshDate
            ];

            if ($currentPluginInfo) {
                if ($this->hasInfoNewerVersion($info['version'], $currentPluginInfo['version'])) {
                    $data['version']        = $currentPluginInfo['version'];
                    $data['update_version'] = $info['version'];
                }

                $this->em->getConnection()->update(
                    's_core_plugins',
                    $data,
                    ['id' => $currentPluginInfo['id']],
                    ['refresh_date' => 'datetime']
                );
            } else {
                $data['added'] = $refreshDate;
                $this->em->getConnection()->insert(
                    's_core_plugins',
                    $data,
                    [
                        'added'        => 'datetime',
                        'refresh_date' => 'datetime',
                    ]
                );
            }
        }
    }

    /**
     * @param Plugin $plugin
     * @param string $file
     */
    private function installForm(Plugin $plugin, $file)
    {
        $xmlConfigReader = new XmlConfigDefinitionReader();
        $config = $xmlConfigReader->read($file);

        $formSynchronizer = new FormSynchronizer($this->em);
        $formSynchronizer->synchronize($plugin, $config);
    }

    /**
     * @param Plugin $plugin
     * @param string $file
     */
    private function installMenu(Plugin $plugin, $file)
    {
        $menuReader = new XmlMenuReader();
        $menu = $menuReader->read($file);

        $menuSynchronizer = new MenuSynchronizer($this->em);
        $menuSynchronizer->synchronize($plugin, $menu);
    }

    /**
     * @param string $updateVersion
     * @param string $currentVersion
     * @return boolean
     */
    private function hasInfoNewerVersion($updateVersion, $currentVersion)
    {
        return version_compare($updateVersion, $currentVersion, '>');
    }

    /**
     * @param Plugin $plugin
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        /** @var Kernel $kernel */
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        return $pluginBootstrap->getPath();
    }

    /**
     * @param string $pluginName
     * @return PluginBootstrap
     */
    private function getPluginByName($pluginName)
    {
        /** @var Kernel $kernel */
        $kernel = Shopware()->Container()->get('kernel');
        $plugins = $kernel->getPlugins();

        if (!isset($plugins[$pluginName])) {
            throw new \InvalidArgumentException(sprintf('Plugin by name "%s" not found.', $pluginName));
        }

        return $plugins[$pluginName];
    }

    /**
     * @param int $pluginId
     */
    private function removeEmotionComponents($pluginId)
    {
        // Remove emotion-components
        $sql = "DELETE s_emotion_element_value, s_emotion_element
                FROM s_emotion_element_value
                RIGHT JOIN s_emotion_element
                    ON s_emotion_element.id = s_emotion_element_value.elementID
                INNER JOIN s_library_component
                    ON s_library_component.id = s_emotion_element.componentID
                    AND s_library_component.pluginID = :pluginId";

        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);

        $sql = "DELETE s_library_component_field, s_library_component
                FROM s_library_component_field
                INNER JOIN s_library_component
                    ON s_library_component.id = s_library_component_field.componentID
                    AND s_library_component.pluginID = :pluginId";

        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     */
    private function removeFormsAndElements($pluginId)
    {
        $sql = <<<SQL
DELETE s_core_config_forms, s_core_config_form_translations, s_core_config_elements, s_core_config_element_translations, s_core_config_values
FROM s_core_config_forms
LEFT JOIN s_core_config_form_translations ON s_core_config_form_translations.form_id = s_core_config_forms.id
LEFT JOIN s_core_config_elements ON s_core_config_elements.form_id = s_core_config_forms.id
LEFT JOIN s_core_config_element_translations ON s_core_config_element_translations.element_id = s_core_config_elements.id
LEFT JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id
WHERE s_core_config_forms.plugin_id = :pluginId
SQL;
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeTemplates($pluginId)
    {
        $sql = 'DELETE FROM s_core_templates WHERE plugin_id = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeMenuEntries($pluginId)
    {
        $sql = 'DELETE FROM s_core_menu WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeCrontabEntries($pluginId)
    {
        $sql = 'DELETE FROM s_crontab WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param int $pluginId
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeEventSubscribers($pluginId)
    {
        $sql = 'DELETE FROM s_core_subscribes WHERE pluginID = :pluginId';
        $this->connection->executeUpdate($sql, [':pluginId' => $pluginId]);
    }
}
