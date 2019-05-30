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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;

class LegacyPluginInstaller
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var \Enlight_Plugin_PluginManager
     */
    private $plugins;

    /**
     * @var array
     */
    private $pluginDirectories;

    /**
     * @param \Enlight_Plugin_PluginManager $plugins
     * @param array                         $pluginDirectories
     */
    public function __construct(ModelManager $em, $plugins, $pluginDirectories)
    {
        $this->em = $em;
        $this->plugins = $plugins;
        $this->pluginDirectories = $pluginDirectories;
    }

    /**
     * Returns a certain plugin by plugin id.
     *
     * @throws \RuntimeException
     *
     * @return \Shopware_Components_Plugin_Bootstrap|null
     */
    public function getPluginBootstrap(Plugin $plugin)
    {
        $namespace = $this->plugins->get($plugin->getNamespace());
        if ($namespace === null) {
            return null;
        }

        $pluginName = $plugin->getName();
        $pluginByName = $namespace->get($pluginName);
        if ($pluginByName === null) {
            throw new \RuntimeException(sprintf('Plugin by name "%s" was not found.', $pluginName));
        }

        return $pluginByName;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function installPlugin(Plugin $plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);

        /** @var \Shopware_Components_Plugin_Namespace $namespace */
        $namespace = $bootstrap->Collection();

        try {
            $result = $namespace->installPlugin($bootstrap);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Unable to install '%s', got exception:\n%s\n", $plugin->getName(), $e->getMessage()), 0, $e);
        }

        $result = is_bool($result) ? ['success' => $result] : $result;

        if (!$result['success']) {
            if (isset($result['message'])) {
                throw new \Exception(sprintf("Unable to install '%s', got message:\n'%s'\n", $plugin->getName(), $result['message']));
            }
            throw new \Exception(sprintf('Unable to install "%s", an unknown error occured.', $plugin->getName()));
        }

        return $result;
    }

    /**
     * @param bool $removeData
     *
     * @throws \Exception
     *
     * @return array
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);

        /** @var \Shopware_Components_Plugin_Namespace $namespace */
        $namespace = $bootstrap->Collection();

        try {
            $result = $namespace->uninstallPlugin($bootstrap, $removeData);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Unable to uninstall '%s', got exception:\n%s\n", $plugin->getName(), $e->getMessage()), 0, $e);
        }

        $result = is_bool($result) ? ['success' => $result] : $result;

        if (!$result['success']) {
            if (isset($result['message'])) {
                throw new \Exception(sprintf("Unable to uninstall '%s', got message:\n%s\n", $plugin->getName(), $result['message']));
            }
            throw new \Exception(sprintf('Unable to uninstall %s, an unknown error occured.', $plugin->getName()));
        }

        return $result;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function updatePlugin(Plugin $plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);

        /** @var \Shopware_Components_Plugin_Namespace $namespace */
        $namespace = $bootstrap->Collection();

        try {
            $result = $namespace->updatePlugin($bootstrap);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("Unable to update '%s', got exception:\n'%s'\n", $plugin->getName(), $e->getMessage()), 0, $e);
        }

        $result = is_bool($result) ? ['success' => $result] : $result;

        if (!$result['success']) {
            if (isset($result['message'])) {
                throw new \Exception(sprintf("Unable to update '%s', got message:\n%s\n", $plugin->getName(), $result['message']));
            }
            throw new \Exception(sprintf('Unable to update "%s", an unknown error occured.', $plugin->getName()));
        }

        return $result;
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function activatePlugin(Plugin $plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);

        $result = $bootstrap->enable();
        $result = is_bool($result) ? ['success' => $result] : $result;

        if ($result['success'] == false) {
            throw new \Exception(sprintf('Not allowed to enable plugin "%s".', $plugin->getName()));
        }

        $plugin->setActive(true);
        $plugin->setInSafeMode(false);
        $this->em->flush($plugin);

        return $result;
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return array
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);

        $result = $bootstrap->disable();
        $result = is_bool($result) ? ['success' => $result] : $result;

        if ($result['success'] == false) {
            throw new \Exception(sprintf('Not allowed to disable plugin "%s".', $plugin->getName()));
        }

        $plugin->setActive(false);
        $this->em->flush($plugin);

        return $result;
    }

    /**
     * @throws \Enlight_Config_Exception
     */
    public function refreshPluginList(\DateTimeInterface $refreshDate)
    {
        /** @var \Shopware_Components_Plugin_Namespace $collection */
        foreach ($this->plugins as $namespace => $collection) {
            if (!$collection instanceof \Shopware_Components_Plugin_Namespace) {
                continue;
            }

            foreach ($this->pluginDirectories as $source => $path) {
                $path .= $namespace;
                if (!is_dir($path)) {
                    continue;
                }

                foreach (new \DirectoryIterator($path) as $dir) {
                    if (!$dir->isDir() || $dir->isDot()) {
                        continue;
                    }
                    $file = $dir->getPathname() . DIRECTORY_SEPARATOR . 'Bootstrap.php';

                    if (!file_exists($file)) {
                        continue;
                    }

                    $name = $dir->getFilename();

                    if ($this->validateIonCube($file)) {
                        throw new \Exception(
                            sprintf(
                                'Plugin "%s" is encrypted but the ionCube Loader extension is not installed',
                                $name
                            )
                        );
                    }

                    $plugin = $collection->get($name);
                    if ($plugin === null) {
                        $plugin = $collection->initPlugin($name, new \Enlight_Config([
                            'source' => $source,
                            'path' => $dir->getPathname() . DIRECTORY_SEPARATOR,
                        ]));
                    }

                    $collection->registerPlugin($plugin, $refreshDate);
                }
            }
        }
    }

    /**
     * @throws \Exception
     *
     * @return string
     */
    public function getPluginPath(Plugin $plugin)
    {
        $baseDir = $this->pluginDirectories[$plugin->getSource()];

        return $baseDir . $plugin->getNamespace() . DIRECTORY_SEPARATOR . $plugin->getName();
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    private function validateIonCube($file)
    {
        if (extension_loaded('ionCube Loader')) {
            return false;
        }

        $content = file_get_contents($file);
        $pos = strpos($content, 'if(!extension_loaded(\'ionCube Loader\')){$__oc=strtolower(');

        return $pos > 0;
    }
}
