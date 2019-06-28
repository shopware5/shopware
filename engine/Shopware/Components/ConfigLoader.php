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

namespace Shopware\Components;

/**
 * This class is responsible to load and parse the shopware configuration
 * files.
 * The Config class is used from the ShopwareKernel to load the shopware
 * configuration before shopware initialed.
 * The ShopwareKernel injects the loaded configuration and the Symfony DI-Container
 * into the Shopware_Application.
 */
class ConfigLoader
{
    /**
     * Contains the document root.
     * This path points to the shopware installation directory.
     *
     * @var string
     */
    protected $documentRoot;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * Contains the environment name.
     *
     * @var string
     */
    protected $environment;

    /**
     * Contains the application name like 'Shopware'.
     *
     * @var string
     */
    protected $applicationName;

    /**
     * @param string $documentRoot
     * @param string $cacheDir
     * @param string $environment
     * @param string $applicationName
     */
    public function __construct($documentRoot, $cacheDir, $environment, $applicationName)
    {
        $this->documentRoot = rtrim($documentRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->cacheDir = $cacheDir;
        $this->environment = $environment;
        $this->applicationName = $applicationName;
    }

    /**
     * Parse the passed configuration file.
     * If the passed configuration file isn't a .php or .inc file,
     * the function throws an exception.
     * This function is used from the ShopwareKernel to load the configuration
     * before the shopware application started.
     * The loaded configuration file will be injected into the Shopware_Application
     *
     * @param string $file
     *
     * @throws \Exception
     *
     * @return array
     */
    public function loadConfig($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (!in_array($suffix, ['php', 'inc'])) {
            throw new \Exception(sprintf('Invalid configuration file provided; unknown config type "%s"', $suffix));
        }

        $config = include $file;

        if (!is_array($config)) {
            throw new \Exception(
                'Invalid configuration file provided; PHP file does not return an array value'
            );
        }

        $config = array_change_key_case($config, CASE_LOWER);

        return $config;
    }

    /**
     * Legacy function for the DocPath function within configuration files.
     *
     * @param string|null $path
     *
     * @return string
     */
    public function DocPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', DIRECTORY_SEPARATOR, $path);

            return $this->documentRoot . $path . DIRECTORY_SEPARATOR;
        }

        return $this->documentRoot;
    }

    /**
     * Legacy function for the AppPath function within configuration files.
     *
     * @param string|null $path
     *
     * @return string
     */
    public function AppPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', DIRECTORY_SEPARATOR, $path);

            return $this->documentRoot . 'engine/Shopware/' . $path . DIRECTORY_SEPARATOR;
        }

        return $this->documentRoot . 'engine/Shopware/';
    }

    /**
     * Legacy function for the TestPath function within configuration files.
     *
     * @param string|null $path
     *
     * @return string
     */
    public function TestPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', DIRECTORY_SEPARATOR, $path);

            return $this->documentRoot . 'tests/Shopware/' . $path . DIRECTORY_SEPARATOR;
        }

        return $this->documentRoot . 'tests/Shopware/';
    }

    /**
     * @return string
     */
    public function Environment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function App()
    {
        return $this->applicationName;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }
}
