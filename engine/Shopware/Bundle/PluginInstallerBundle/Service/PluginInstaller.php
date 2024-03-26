<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Enlight_Event_EventManager;
use Exception;
use InvalidArgumentException;
use PDO;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Bundle\PluginInstallerBundle\Events\PluginEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PostPluginActivateEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PostPluginDeactivateEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PostPluginInstallEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PostPluginUninstallEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PostPluginUpdateEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PrePluginActivateEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PrePluginDeactivateEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PrePluginInstallEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PrePluginUninstallEvent;
use Shopware\Bundle\PluginInstallerBundle\Events\PrePluginUpdateEvent;
use Shopware\Components\Migrations\AbstractMigration;
use Shopware\Components\Migrations\AbstractPluginMigration;
use Shopware\Components\Migrations\PluginMigrationManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin as PluginBaseClass;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\CronjobSynchronizer;
use Shopware\Components\Plugin\FormSynchronizer;
use Shopware\Components\Plugin\MenuSynchronizer;
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\Plugin\XmlReader\XmlConfigReader;
use Shopware\Components\Plugin\XmlReader\XmlCronjobReader;
use Shopware\Components\Plugin\XmlReader\XmlMenuReader;
use Shopware\Components\Plugin\XmlReader\XmlPluginReader;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Components\Snippet\DatabaseHandler;
use Shopware\Kernel;
use Shopware\Models\Plugin\Plugin;

class PluginInstaller
{
    private ModelManager $em;

    private Connection $connection;

    private DatabaseHandler $snippetHandler;

    private RequirementValidator $requirementValidator;

    private PDO $pdo;

    /**
     * @var array<string, string>
     */
    private array $pluginDirectories;

    private ShopwareReleaseStruct $release;

    private Enlight_Event_EventManager $events;

    private LoggerInterface $logger;

    private Kernel $kernel;

    /**
     * @param array<string, string> $pluginDirectories
     */
    public function __construct(
        ModelManager $em,
        DatabaseHandler $snippetHandler,
        RequirementValidator $requirementValidator,
        PDO $pdo,
        Enlight_Event_EventManager $events,
        array $pluginDirectories,
        ShopwareReleaseStruct $release,
        LoggerInterface $logger,
        Kernel $kernel
    ) {
        $this->em = $em;
        $this->connection = $this->em->getConnection();
        $this->snippetHandler = $snippetHandler;
        $this->requirementValidator = $requirementValidator;
        $this->pdo = $pdo;
        $this->events = $events;
        $this->pluginDirectories = $pluginDirectories;
        $this->release = $release;
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     *
     * @return InstallContext
     */
    public function installPlugin(Plugin $plugin)
    {
        $pluginBootstrap = $this->getPluginByName($plugin->getName());

        $context = new InstallContext($plugin, $this->release->getVersion(), $plugin->getVersion());

        $this->requirementValidator->validate($pluginBootstrap->getPath() . '/plugin.xml', $this->release->getVersion());

        $this->events->notify(PluginEvent::PRE_INSTALL, new PrePluginInstallEvent($context, $pluginBootstrap));
        $this->installResources($pluginBootstrap, $plugin);

        // Makes sure the version is updated in the db after a re-installation
        if ($plugin->getUpdateVersion() && $this->hasInfoNewerVersion($plugin->getUpdateVersion(), $plugin->getVersion())) {
            $plugin->setVersion($plugin->getUpdateVersion());
        }

        $this->em->flush($plugin);

        $this->applyMigrations($pluginBootstrap, AbstractMigration::MODUS_INSTALL);

        $pluginBootstrap->install($context);

        $this->events->notify(PluginEvent::POST_INSTALL, new PostPluginInstallEvent($context, $pluginBootstrap));

        $plugin->setInstalled(new DateTime());
        $plugin->setUpdated(new DateTime());

        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @param bool $removeData
     *
     * @throws Exception
     * @throws DBALException
     * @throws OptimisticLockException
     *
     * @return UninstallContext
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        $context = new UninstallContext($plugin, $this->release->getVersion(), $plugin->getVersion(), !$removeData);
        $bootstrap = $this->getPluginByName($plugin->getName());

        $this->events->notify(PluginEvent::PRE_DEACTIVATE, new PrePluginDeactivateEvent($context, $bootstrap));
        $this->events->notify(PluginEvent::PRE_UNINSTALL, new PrePluginUninstallEvent($context, $bootstrap));

        $this->applyMigrations($bootstrap, AbstractPluginMigration::MODUS_UNINSTALL, !$removeData);

        $bootstrap->uninstall($context);

        $plugin->setInstalled(null);
        $plugin->setActive(false);

        $this->events->notify(PluginEvent::POST_UNINSTALL, new PostPluginUninstallEvent($context, $bootstrap));
        $this->events->notify(PluginEvent::POST_DEACTIVATE, new PostPluginDeactivateEvent($context, $bootstrap));

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
     * @throws Exception
     *
     * @return UpdateContext
     */
    public function updatePlugin(Plugin $plugin)
    {
        $pluginBootstrap = $this->getPluginByName($plugin->getName());
        $this->requirementValidator->validate($pluginBootstrap->getPath() . '/plugin.xml', $this->release->getVersion());

        $context = new UpdateContext(
            $plugin,
            $this->release->getVersion(),
            $plugin->getVersion(),
            $plugin->getUpdateVersion()
        );

        $this->events->notify(PluginEvent::PRE_UPDATE, new PrePluginUpdateEvent($context, $pluginBootstrap));

        $this->installResources($pluginBootstrap, $plugin);

        $this->applyMigrations($pluginBootstrap, AbstractMigration::MODUS_UPDATE);

        $pluginBootstrap->update($context);

        $this->events->notify(PluginEvent::POST_UPDATE, new PostPluginUpdateEvent($context, $pluginBootstrap));

        $plugin->setVersion($context->getUpdateVersion());
        $plugin->setUpdateVersion(null);
        $plugin->setUpdateSource(null);
        $plugin->setUpdated(new DateTime());

        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @throws Exception
     * @throws OptimisticLockException
     *
     * @return ActivateContext
     */
    public function activatePlugin(Plugin $plugin)
    {
        $bootstrap = $this->getPluginByName($plugin->getName());
        $this->requirementValidator->validate($bootstrap->getPath() . '/plugin.xml', $this->release->getVersion());
        $context = new ActivateContext($plugin, $this->release->getVersion(), $plugin->getVersion());

        $this->events->notify(PluginEvent::PRE_ACTIVATE, new PrePluginActivateEvent($context, $bootstrap));

        $bootstrap->activate($context);

        $this->events->notify(PluginEvent::POST_ACTIVATE, new PostPluginActivateEvent($context, $bootstrap));

        $plugin->setActive(true);
        $plugin->setInSafeMode(false);
        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @throws Exception
     * @throws OptimisticLockException
     *
     * @return DeactivateContext
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        $context = new DeactivateContext($plugin, $this->release->getVersion(), $plugin->getVersion());
        $bootstrap = $this->getPluginByName($plugin->getName());

        $this->events->notify(PluginEvent::PRE_DEACTIVATE, new PrePluginDeactivateEvent($context, $bootstrap));

        $bootstrap->deactivate($context);

        $plugin->setActive(false);
        $this->events->notify(PluginEvent::POST_DEACTIVATE, new PostPluginDeactivateEvent($context, $bootstrap));

        $this->em->flush($plugin);

        return $context;
    }

    /**
     * @throws RuntimeException
     *
     * @return void
     */
    public function refreshPluginList(DateTimeInterface $refreshDate)
    {
        $refreshDateString = $refreshDate->format('Y-m-d H:i:s');
        $initializer = new PluginInitializer(
            $this->pdo,
            $this->pluginDirectories
        );

        foreach ($initializer->initializePlugins() as $plugin) {
            $pluginInfoPath = $plugin->getPath() . '/plugin.xml';
            if (is_file($pluginInfoPath)) {
                $info = (new XmlPluginReader())->read($pluginInfoPath);
            } else {
                $info = [];
            }

            $currentPluginInfo = $this->em->getConnection()->fetchAssociative(
                'SELECT * FROM s_core_plugins WHERE `name` LIKE ?',
                [$plugin->getName()]
            );

            $translations = [];
            $translatableInfoKeys = ['label', 'description'];
            foreach ($info as $key => $value) {
                if (!\in_array($key, $translatableInfoKeys, true)) {
                    continue;
                }

                foreach ($value as $lang => $translation) {
                    $translations[$lang][$key] = $translation;
                }
            }

            $info['label'] = (string) ($info['label']['en'] ?? $plugin->getName());
            $info['description'] = $info['description']['en'] ?? null;
            $info['version'] = (string) ($info['version'] ?? '0.0.1');
            $info['author'] = $info['author'] ?? null;
            $info['link'] = $info['link'] ?? null;

            $data = [
                'namespace' => $plugin->getPluginNamespace(),
                'version' => $info['version'],
                'author' => $info['author'] ? (string) $info['author'] : null,
                'name' => $plugin->getName(),
                'link' => $info['link'] ? (string) $info['link'] : null,
                'label' => $info['label'],
                'description' => $info['description'] ? (string) $info['description'] : null,
                'capability_update' => 1,
                'capability_install' => 1,
                'capability_enable' => 1,
                'capability_secure_uninstall' => 1,
                'refresh_date' => $refreshDateString,
                'translations' => $translations ? json_encode($translations, JSON_THROW_ON_ERROR) : null,
                'changes' => isset($info['changelog']) ? json_encode($info['changelog'], JSON_THROW_ON_ERROR) : null,
            ];

            if ($currentPluginInfo) {
                if ($this->hasInfoNewerVersion($info['version'], $currentPluginInfo['version'])) {
                    $data['version'] = (string) $currentPluginInfo['version'];
                    $data['update_version'] = $info['version'];
                }

                $this->em->getConnection()->update(
                    's_core_plugins',
                    $data,
                    ['id' => (int) $currentPluginInfo['id']],
                    ['refresh_date' => 'datetime']
                );
            } else {
                $data['added'] = $refreshDateString;
                $this->em->getConnection()->insert(
                    's_core_plugins',
                    $data,
                    [
                        'added' => 'datetime',
                        'refresh_date' => 'datetime',
                    ]
                );
            }
        }
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        return $this->getPluginByName($plugin->getName())->getPath();
    }

    private function removeSnippets(PluginBaseClass $bootstrap, bool $removeDirty): void
    {
        $this->snippetHandler->removeFromDatabase($bootstrap->getPath() . '/Resources/snippets/', $removeDirty);
    }

    /**
     * @throws Exception
     */
    private function installResources(PluginBaseClass $bootstrap, Plugin $plugin): void
    {
        if (is_file($bootstrap->getPath() . '/Resources/config.xml')) {
            $this->installForm($plugin, $bootstrap->getPath() . '/Resources/config.xml');
        }

        if (is_file($bootstrap->getPath() . '/Resources/menu.xml')) {
            $this->installMenu($plugin, $bootstrap->getPath() . '/Resources/menu.xml');
        }

        if (is_file($bootstrap->getPath() . '/Resources/cronjob.xml')) {
            $this->installCronjob($plugin, $bootstrap->getPath() . '/Resources/cronjob.xml');
        }

        if (file_exists($bootstrap->getPath() . '/Resources/snippets')) {
            $this->installSnippets($bootstrap);
        }
    }

    private function installSnippets(PluginBaseClass $bootstrap): void
    {
        $this->snippetHandler->loadToDatabase($bootstrap->getPath() . '/Resources/snippets/');
    }

    /**
     * @throws Exception
     */
    private function installForm(Plugin $plugin, string $file): void
    {
        $config = (new XmlConfigReader())->read($file);

        $formSynchronizer = new FormSynchronizer($this->em);
        $formSynchronizer->synchronize($plugin, $config);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function installMenu(Plugin $plugin, string $file): void
    {
        $menu = (new XmlMenuReader())->read($file);

        $menuSynchronizer = new MenuSynchronizer($this->em);
        $menuSynchronizer->synchronize($plugin, $menu);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function installCronjob(Plugin $plugin, string $file): void
    {
        $cronjobs = (new XmlCronjobReader())->read($file);

        $cronjobSynchronizer = new CronjobSynchronizer($this->em->getConnection());
        $cronjobSynchronizer->synchronize($plugin, $cronjobs);
    }

    private function hasInfoNewerVersion(string $updateVersion, string $currentVersion): bool
    {
        return version_compare($updateVersion, $currentVersion, '>');
    }

    /**
     * @throws Exception
     */
    private function getPluginByName(string $pluginName): PluginBaseClass
    {
        $plugins = $this->kernel->getPlugins();

        if (!isset($plugins[$pluginName])) {
            throw new InvalidArgumentException(sprintf('Plugin by name "%s" not found.', $pluginName));
        }

        return $plugins[$pluginName];
    }

    /**
     * @throws DBALException
     */
    private function removeEmotionComponents(int $pluginId): void
    {
        // Remove emotion-components
        $sql = 'DELETE s_emotion_element_value, s_emotion_element
                FROM s_emotion_element_value
                RIGHT JOIN s_emotion_element
                    ON s_emotion_element.id = s_emotion_element_value.elementID
                INNER JOIN s_library_component
                    ON s_library_component.id = s_emotion_element.componentID
                    AND s_library_component.pluginID = :pluginId';

        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);

        $sql = 'DELETE s_library_component_field, s_library_component
                FROM s_library_component_field
                INNER JOIN s_library_component
                    ON s_library_component.id = s_library_component_field.componentID
                    AND s_library_component.pluginID = :pluginId';

        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @throws DBALException
     */
    private function removeFormsAndElements(int $pluginId): void
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
        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @throws DBALException
     */
    private function removeTemplates(int $pluginId): void
    {
        $sql = 'DELETE FROM s_core_templates WHERE plugin_id = :pluginId';
        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @throws DBALException
     */
    private function removeMenuEntries(int $pluginId): void
    {
        $menuItems = $this->em->getConnection()->createQueryBuilder()
            ->select(['id', 'controller', 'action'])
            ->from('s_core_menu')
            ->andWhere('pluginID = :pluginId')
            ->setParameter(':pluginId', $pluginId)
            ->execute()
            ->fetchAllAssociative();

        if (\count($menuItems) === 0) {
            return;
        }

        $deleteSnippets = [];

        foreach ($menuItems as $menuItem) {
            $name = $menuItem['controller'];

            // Index actions aren't appended to the name of the snippet, they are an exemption from the rule
            if ($menuItem['action'] !== 'Index') {
                $name .= '/' . $menuItem['action'];
            }

            $deleteSnippets[] = $name;
        }

        $this->em->getConnection()->executeQuery(
            'DELETE FROM s_core_snippets WHERE namespace = "backend/index/view/main" AND `name` IN (:names)',
            ['names' => $deleteSnippets],
            ['names' => Connection::PARAM_STR_ARRAY]
        );

        $sql = 'DELETE FROM s_core_menu WHERE pluginID = :pluginId';
        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @throws DBALException
     */
    private function removeCrontabEntries(int $pluginId): void
    {
        $sql = 'DELETE FROM s_crontab WHERE pluginID = :pluginId';
        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @throws DBALException
     */
    private function removeEventSubscribers(int $pluginId): void
    {
        $sql = 'DELETE FROM s_core_subscribes WHERE pluginID = :pluginId';
        $this->connection->executeStatement($sql, [':pluginId' => $pluginId]);
    }

    /**
     * @param AbstractPluginMigration::MODUS_* $mode
     */
    private function applyMigrations(PluginBaseClass $plugin, string $mode, bool $keepUserData = false): void
    {
        $manager = new PluginMigrationManager($this->pdo, $plugin, $this->logger);
        if (!is_dir($manager->getMigrationPath())) {
            return;
        }
        $manager->run($mode, $keepUserData);
    }
}
