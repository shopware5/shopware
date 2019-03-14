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
 */
class Shopware extends Enlight_Application
{
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
     * @deprecated 4.2
     */
    public function __call($name, $value = null)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $caller = $trace['file'] . ':' . $trace['line'];

        trigger_error('Shopware()->' . $name . '() is deprecated since version 4.2 and will be removed in 6.0. Use the Container instead. Called by ' . $caller, E_USER_DEPRECATED);

        if (!$this->container->has($name)) {
            throw new Enlight_Exception(
                sprintf('Method "%s::%s" not found failure', get_class($this), $name),
                Enlight_Exception::METHOD_NOT_FOUND
            );
        }

        return $this->container->get($name);
    }

    /**
     * Returns the name of the application
     *
     * @deprecated since 5.2, to be removed in 6.0
     *
     * @return string
     */
    public function App()
    {
        trigger_error('Shopware()->App() is deprecated since version 5.2 and will be removed in 6.0.', E_USER_DEPRECATED);

        return $this->container->getParameter('kernel.name');
    }

    /**
     * Returns the application environment method
     *
     * @deprecated since 5.2, to be removed in 6.0
     *
     * @return string
     */
    public function Environment()
    {
        trigger_error('Shopware()->Environment() is deprecated since version 5.2 and will be removed in 6.0. Use the kernel.environment parameter instead.', E_USER_DEPRECATED);

        return $this->container->getParameter('kernel.environment');
    }

    /**
     * @deprecated since 5.2, to be removed in 6.0. Use Shopware()->DocPath() instead.
     *
     * @param string $path
     *
     * @return string
     */
    public function OldPath($path = null)
    {
        trigger_error('Shopware()->OldPath() is deprecated since version 5.2 and will be removed in 6.0. Use Shopware()->DocPath() instead.', E_USER_DEPRECATED);

        return $this->DocPath($path);
    }

    /**
     * Returns document path: <project root>/
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
     * Returns the application path: <project root>/engine/Shopware/
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
     *
     * @return sSystem
     */
    public function System()
    {
        return $this->container->get('system');
    }

    /**
     * Returns front controller instance
     *
     * @return Enlight_Controller_Front|null
     */
    public function Front()
    {
        return $this->container->get('front');
    }

    /**
     * @return Enlight_Template_Manager|null
     */
    public function Template()
    {
        return $this->container->get('template');
    }

    /**
     * @return Shopware_Components_Config|null
     */
    public function Config()
    {
        return $this->container->get('config');
    }

    /**
     * Returns access layer to deprecated shopware frontend objects
     *
     * @return Shopware_Components_Modules|null
     */
    public function Modules()
    {
        return $this->container->get('modules');
    }

    /**
     * @return \Shopware\Models\Shop\DetachedShop|null
     */
    public function Shop()
    {
        return $this->container->get('shop');
    }

    /**
     * Returns database instance
     *
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql|null
     */
    public function Db()
    {
        return $this->container->get('db');
    }

    /**
     * @return Shopware\Components\Model\ModelManager|null
     */
    public function Models()
    {
        return $this->container->get('models');
    }

    /**
     * @return Enlight_Components_Session_Namespace|null
     */
    public function Session()
    {
        return $this->container->get('session');
    }

    /**
     * @return Enlight_Components_Session_Namespace|null
     */
    public function BackendSession()
    {
        return $this->container->get('backendsession');
    }

    /**
     * @return Shopware_Components_Acl|null
     */
    public function Acl()
    {
        return $this->container->get('acl');
    }

    /**
     * @return Shopware_Components_TemplateMail|null
     */
    public function TemplateMail()
    {
        return $this->container->get('templatemail');
    }

    /**
     * @return Enlight_Plugin_PluginManager|null
     */
    public function Plugins()
    {
        return $this->container->get('plugin_manager');
    }

    /**
     * @return Shopware_Components_Snippet_Manager|null
     */
    public function Snippets()
    {
        return $this->container->get('snippets');
    }

    /**
     * @return \Shopware\Components\Password\Manager|null
     */
    public function PasswordEncoder()
    {
        return $this->container->get('passwordencoder');
    }

    /**
     * @return Enlight_Event_EventManager|null
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
     * @deprecated since 5.2, to be removed in 6.0
     */
    public function setEventManager(Enlight_Event_EventManager $manager)
    {
        trigger_error('Shopware()->setEventManager() is deprecated since version 5.2 and will be removed in 6.0. Use the Container instead.', E_USER_DEPRECATED);

        $this->container->set('events', $manager);
    }

    /**
     * Returns the instance of the application bootstrap
     *
     * @deprecated since 5.2, to be removed in 6.0
     *
     * @return Shopware_Bootstrap
     */
    public function Bootstrap()
    {
        trigger_error('Shopware()->Bootstrap() is deprecated since version 5.2 and will be removed in 6.0. Use the Container instead.', E_USER_DEPRECATED);

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
