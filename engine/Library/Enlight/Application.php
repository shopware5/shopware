<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * The Enlight_Application component forms the basis for the enlight project.
 *
 * Creates an new application with the passed configuration. If no configuration is given, enlight loads
 * the configuration automatically. It loads the different resources, for example classes, loader or the
 * managers for the different packages (Hook, Plugin, Event).
 *
 * @deprecated since 5.2, to be removed in 6.0
 * @category   Enlight
 * @package    Enlight_Application
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Application
{
    /**
     * @deprecated since 5.2, to be removed in 6.0
     * @var string
     */
    protected $path;

    /**
     * @deprecated since 5.2, to be removed in 6.0
     * @var string
     */
    protected $core_path;

    /**
     * @deprecated since 5.2, to be removed in 6.0
     * @var Enlight_Application Instance of the Enlight application.
     * Will be set in the class constructor.
     */
    protected static $instance;

    public function __construct()
    {
        self::$instance = $this;

        $this->core_path = __DIR__ . DIRECTORY_SEPARATOR;
        $this->path      = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns directory separator
     * @deprecated since 5.2, to be removed in 6.0
     * @return string
     */
    public static function DS()
    {
        trigger_error('Enlight_Application::DS() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the instance of the application
     *
     * @deprecated since 5.2, to be removed in 6.0
     * @return Enlight_Application
     */
    public static function Instance()
    {
        trigger_error('Enlight_Application::Instance() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return self::$instance;
    }

    /**
     * Returns the enlight path: <projectroot>/engine/Library/Enlight/
     *
     * @deprecated since 5.2, to be removed in 6.0
     * @param string $path
     * @return string
     */
    public function CorePath($path = null)
    {
        trigger_error('Enlight_Application::CorePath() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return $this->normalizePath($this->core_path, $path);
    }

    /**
     * Returns the enlight components path: <projectroot>/engine/Library/Enlight/Components/
     *
     * @deprecated since 5.2, to be removed in 6.0
     * @param string $path
     * @return string
     */
    public function ComponentsPath($path = null)
    {
        trigger_error('Enlight_Application::ComponentsPath() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return $this->normalizePath($this->core_path . 'Components' . DIRECTORY_SEPARATOR, $path);
    }


    /**
     * Returns the library path: <projectroot>/engine/Library/
     *
     * @deprecated since 5.2, to be removed in 6.0
     * @param string $path
     * @return string
     */
    public function Path($path = null)
    {
        trigger_error('Enlight_Application::Path() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return $this->normalizePath($this->path, $path);
    }

    /**
     * @param string $basePath
     * @param string|null $path
     * @return string
     */
    private function normalizePath($basePath, $path = null)
    {
        if ($path === null) {
            return $basePath;
        }

        $path = str_replace('_', DIRECTORY_SEPARATOR, $path);

        return $basePath . $path . DIRECTORY_SEPARATOR;
    }
}

/**
 * Proxy to Shopware()
 *
 * @deprecated since 5.2, to be removed in 6.0
 * @param   Enlight_Application $newInstance
 * @return  Enlight_Application
 */
function Enlight($newInstance = null)
{
    trigger_error('Enlight() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware() instead.', E_USER_DEPRECATED);

    return Shopware($newInstance);
}
