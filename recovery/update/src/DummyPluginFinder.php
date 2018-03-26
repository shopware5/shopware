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

namespace Shopware\Recovery\Update;

class DummyPluginFinder
{
    /**
     * @var string
     */
    private $shopwarePath;

    /**
     * @param string $shopwarePath
     */
    public function __construct($shopwarePath)
    {
        $this->shopwarePath = $shopwarePath;
    }

    /**
     * @return string[]
     */
    public function getDummyPlugins()
    {
        $pluginPath = $this->shopwarePath . '/engine/Shopware/Plugins/Default';
        $types = ['Backend', 'Core', 'Frontend'];
        $plugins = [];

        foreach ($types as $type) {
            foreach (new \DirectoryIterator($pluginPath . '/' . $type) as $dir) {
                if (!$dir->isDir() || $dir->isDot()) {
                    continue;
                }

                if ($this->isDummyPlugin($dir->getPathname())) {
                    $plugins[] = $dir->getPathname();
                }
            }
        }

        return $plugins;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isDummyPlugin($path)
    {
        $bootstrapFile = $path . '/Bootstrap.php';
        $dummyBootstrapFile = $path . '/BootstrapDummy.php';

        if (is_file($dummyBootstrapFile) && !is_file($bootstrapFile)) {
            return true;
        }

        $needle = 'Shopware_Components_DummyPlugin_Bootstrap';
        $contents = file_get_contents($bootstrapFile);
        if (stripos($contents, $needle) !== false) {
            return true;
        }

        return false;
    }
}
