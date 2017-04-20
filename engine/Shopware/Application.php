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
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware extends Enlight_Application
{
    const VERSION = '___VERSION___';
    const VERSION_TEXT = '___VERSION_TEXT___';
    const REVISION = '___REVISION___';

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
        $this->appPath = __DIR__ . DIRECTORY_SEPARATOR;
        $this->docPath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;

        parent::__construct();
    }

    /**
     * Returns called resource
     *
     * @param string $name
     * @param array  $value
     *
     * @throws Enlight_Exception
     *
     * @return mixed
     *
     * @deprecated 4.2
     */
    public function __call($name, $value = null)
    {
        trigger_error('Shopware()->__call(' . $name . ') is deprecated since version 4.2 and will be removed in 5.3. Use the Container instead.', E_USER_DEPRECATED);

        if (!$this->container->has($name)) {
            throw new Enlight_Exception(
                'Method "' . get_class($this) . '::' . $name . '" not found failure',
                Enlight_Exception::METHOD_NOT_FOUND
            );
        }

        return $this->container->get($name);
    }

    /**
     * Returns the name of the application
     *
     * @deprecated since 5.2, to be removed in 5.3
     *
     * @return string
     */
    public function App()
    {
        trigger_error('Shopware()->App() is deprecated since version 5.2 and will be removed in 5.3.', E_USER_DEPRECATED);

        return $this->container->getParameter('kernel.name');
    }

    /**
     * Returns the application environment method
     *
     * @deprecated since 5.2, to be removed in 5.3
     *
     * @return string
     */
    public function Environment()
    {
        trigger_error('Shopware()->Environment() is deprecated since version 5.2 and will be removed in 5.3. Use the kernel.environment parameter instead.', E_USER_DEPRECATED);

        return $this->container->getParameter('kernel.environment');
    }

    /**
     * @deprecated since 5.2, to be removed in 5.3. Use Shopware()->DocPath() instead.
     *
     * @param string $path
     *
     * @return string
     */
    public function OldPath($path = null)
    {
        trigger_error('Shopware()->OldPath() is deprecated since version 5.2 and will be removed in 5.3. Use Shopware()->DocPath() instead.', E_USER_DEPRECATED);

        return $this->DocPath($path);
    }

    /**
     * Returns document path: <projectroot>/
     *
     * @param string $path
     *
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
     *
     * @return string
     */
    public function AppPath($path = null)
    {
        return $this->normalizePath($this->appPath, $path);
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
     * Returns the instance of the loader, which is initialed in the class constructor
     *
     * @return Enlight_Loader
     */
    public function Loader()
    {
        return $this->container->get('loader');
    }

    /**
     * Returns the instance of the hook manager, which is initialed in the class constructor
     *
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
     *
     * @return sSystem
     */
    public function System()
    {
        return $this->container->get('System');
    }

    /**
     * Returns front controller instance
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        return $this->container->get('Front');
    }

    /**
     * Returns template instance
     *
     * @return Enlight_Template_Manager
     */
    public function Template()
    {
        return $this->container->get('Template');
    }

    /**
     * Returns config instance
     *
     * @return Shopware_Components_Config
     */
    public function Config()
    {
        return $this->container->get('Config');
    }

    /**
     * Returns access layer to deprecated shopware frontend objects
     *
     * @return Shopware_Components_Modules
     */
    public function Modules()
    {
        return $this->container->get('Modules');
    }

    /**
     * @return \Shopware\Models\Shop\DetachedShop
     */
    public function Shop()
    {
        return $this->container->get('Shop');
    }

    /**
     * Returns database instance
     *
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function Db()
    {
        return $this->container->get('Db');
    }

    /**
     * Returns doctrine instance
     *
     * @return Shopware\Components\Model\ModelManager
     */
    public function Models()
    {
        return $this->container->get('Models');
    }

    /**
     * Returns session instance
     *
     * @return Enlight_Components_Session_Namespace
     */
    public function Session()
    {
        return $this->container->get('Session');
    }

    /**
     * Returns session instance
     *
     * @return Shopware_Components_Acl
     */
    public function Acl()
    {
        return $this->container->get('Acl');
    }

    /**
     * Returns session instance
     *
     * @return Shopware_Components_TemplateMail
     */
    public function TemplateMail()
    {
        return $this->container->get('TemplateMail');
    }

    /**
     * Returns the instance of the plugin manager, which is initialed in the class constructor
     *
     * @return Enlight_Plugin_PluginManager
     */
    public function Plugins()
    {
        return $this->container->get('plugin_manager');
    }

    /**
     * Returns the instance of the snippet manager
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function Snippets()
    {
        return $this->container->get('snippets');
    }

    /**
     * Returns the instance of the password manager
     *
     * @return \Shopware\Components\Password\Manager
     */
    public function PasswordEncoder()
    {
        return $this->container->get('PasswordEncoder');
    }

    /**
     * Returns the instance of the event manager, which is initialed in the class constructor
     *
     * @return Enlight_Event_EventManager
     */
    public function Events()
    {
        return $this->container->get('events');
    }

    /**
     * Setter function of the _events property.
     * Allows to override the default Shopware Event Manager with an
     * plugin specified event manager.
     * The passed manager has to be an instance of the Enlight_Event_EventManager,
     * otherwise the function throws an exception.
     *
     * @deprecated since 5.2, to be removed in 5.3
     *
     * @param Enlight_Event_EventManager $manager
     */
    public function setEventManager(Enlight_Event_EventManager $manager)
    {
        trigger_error('Shopware()->setEventManager() is deprecated since version 5.2 and will be removed in 5.3. Use the Container instead.', E_USER_DEPRECATED);

        $this->container->set('events', $manager);
    }

    /**
     * Returns the instance of the application bootstrap
     *
     * @deprecated since 5.2, to be removed in 5.3
     *
     * @return Shopware_Bootstrap
     */
    public function Bootstrap()
    {
        trigger_error('Shopware()->Bootstrap() is deprecated since version 5.2 and will be removed in 5.3. Use the Container instead.', E_USER_DEPRECATED);

        return $this->container->get('bootstrap');
    }

    /**
     * @param string      $basePath
     * @param string|null $path
     *
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
 * Returns application instance
 *
 * @param Shopware $newInstance
 *
 * @return Shopware
 */
function Shopware($newInstance = null)
{
    static $instance;
    if (isset($newInstance)) {
        $oldInstance = $instance;
        $instance = $newInstance;

        return $oldInstance;
    } elseif (!isset($instance)) {
        throw new RuntimeException('Shopware Kernel not booted');
    }

    return $instance;
}
