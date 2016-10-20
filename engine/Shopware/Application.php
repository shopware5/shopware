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

use Shopware\Components\DependencyInjection\Container;

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware
{
    const VERSION      = '___VERSION___';
    const VERSION_TEXT = '___VERSION_TEXT___';
    const REVISION     = '___REVISION___';

    /**
     * @var string
     */
    protected $appPath;

    /**
     * @var string
     */
    protected $docPath;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        // Initialize global Shopware function
        Shopware($this);

        $this->container = $container;
        $this->appPath   = __DIR__ . DIRECTORY_SEPARATOR;
        $this->docPath   = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns document path: <projectroot>/
     *
     * @param string $path
     * @return string
     */
    public function DocPath($path = null)
    {
        return $this->normalizePath($this->docPath, $path);
    }

    /**
     * Returns the application path: <projectroot>/engine/Shopware/
     *
     * @param string $path
     * @return string
     */
    public function AppPath($path = null)
    {
        return $this->normalizePath($this->appPath, $path);
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

    /**
     * Returns injection container
     *
     * @return Container
     */
    public function Container()
    {
        return $this->container;
    }

    /**
     * @return Enlight_Loader
     */
    public function Loader()
    {
        return $this->container->get('loader');
    }

    /**
     * @return Enlight_Hook_HookManager
     */
    public function Hooks()
    {
        return $this->container->get('hooks');
    }

    /**
     * Returns the system configuration
     *
     * @deprecated sSystem is deprecated
     * @return sSystem
     */
    public function System()
    {
        return $this->container->get('system');
    }

    /**
     * Returns front controller instance
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        return $this->container->get('front');
    }

    /**
     * @return Enlight_Template_Manager
     */
    public function Template()
    {
        return $this->container->get('template');
    }

    /**
     * @return Shopware_Components_Config
     */
    public function Config()
    {
        return $this->container->get('config');
    }

    /**
     * Returns access layer to deprecated shopware frontend objects
     *
     * @return Shopware_Components_Modules
     */
    public function Modules()
    {
        return $this->container->get('modules');
    }

    /**
     * @return \Shopware\Models\Shop\DetachedShop
     */
    public function Shop()
    {
        return $this->container->get('shop');
    }

    /**
     * Returns database instance
     *
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function Db()
    {
        return $this->container->get('db');
    }

    /**
     * @return Shopware\Components\Model\ModelManager
     */
    public function Models()
    {
        return $this->container->get('models');
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    public function Session()
    {
        return $this->container->get('session');
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    public function BackendSession()
    {
        return $this->container->get('backendsession');
    }

    /**
     * @return Shopware_Components_Acl
     */
    public function Acl()
    {
        return $this->container->get('acl');
    }

    /**
     * @return Shopware_Components_TemplateMail
     */
    public function TemplateMail()
    {
        return $this->container->get('templatemail');
    }

    /**
     * @return Enlight_Plugin_PluginManager
     */
    public function Plugins()
    {
        return $this->container->get('plugin_manager');
    }

    /**
     * @return Shopware_Components_Snippet_Manager
     */
    public function Snippets()
    {
        return $this->container->get('snippets');
    }

    /**
     * @return \Shopware\Components\Password\Manager
     */
    public function PasswordEncoder()
    {
        return $this->container->get('passwordencoder');
    }

    /**
     * @return Enlight_Event_EventManager
     */
    public function Events()
    {
        return $this->container->get('events');
    }

    /**
     * Returns called resource
     *
     * @throws Enlight_Exception
     * @param string $name
     * @param array $value
     * @return mixed
     * @deprecated 4.2
     */
    public function __call($name, $value = null)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $caller = $trace['file'].':'.$trace['line'];

        trigger_error('Shopware()->'. $name . '() is deprecated since version 4.2 and will be removed in 6.0. Use the Container instead. Called by '. $caller, E_USER_DEPRECATED);

        if (!$this->container->has($name)) {
            throw new Enlight_Exception(
                'Method "' . get_class($this) . '::' . $name . '" not found failure',
                Enlight_Exception::METHOD_NOT_FOUND
            );
        }

        return $this->container->get($name);
    }
}

/**
 * Returns application instance
 *
 * @param   Shopware $newInstance
 * @return  Shopware
 */
function Shopware($newInstance = null)
{
    static $instance;
    if (isset($newInstance)) {
        $oldInstance = $instance;
        $instance    = $newInstance;
        return $oldInstance;
    } elseif (!isset($instance)) {
        throw new RuntimeException('Shopware Kernel not booted');
    }

    return $instance;
}
