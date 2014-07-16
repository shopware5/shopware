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
 * @package    Enlight_Application
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

use Shopware\Components\DependencyInjection\Container;

/**
 * The Enlight_Application component forms the basis for the enlight project.
 *
 * Creates an new application with the passed configuration. If no configuration is given, enlight loads
 * the configuration automatically. It loads the different resources, for example classes, loader or the
 * managers for the different packages (Hook, Plugin, Event).
 * Afterwards the bootstrap can be loaded by the run method. The individual project resources can be loaded in the
 * Enlight_Bootstrap over the configuration.
 *
 * @category   Enlight
 * @package    Enlight_Application
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Application
{
    /**
     * @var string The application environment can be set in the class constructor. It is used when generating the config
     */
    protected $environment;

    /**
     * @var array The options property contains the different settings for the enlight application,
     * for example auto loader namespaces, include paths or the php settings.
     */
    protected $options;

    /**
     * @var string Directory separator
     */
    protected static $ds = DIRECTORY_SEPARATOR;

    /**
     * @var Enlight_Application Instance of the Enlight application.
     * Will be set in the class constructor.
     */
    protected static $instance;

    /**
     * @var string The path property contains the framework path.
     * It will used to return the specified paths like, core_path or app_path.
     */
    protected $path;

    /**
     * @var string Contains the name of the application.
     * Can be set in the class constructor parameter options["app"].
     * If no application name given the default name "Default" will be set.
     */
    protected $app;

    /**
     * @var string Contains the path of the application.
     */
    protected $appPath;

    /**
     * @var string Contains the path of the framework core.
     */
    protected $core_path;

    /**
     * @var Enlight_Loader The Enlight_Loader register the application namespaces.
     */
    protected $_loader;

    /**
     * @var Enlight_Hook_HookManager Instance of the Enlight_Hook_HookManager which contains all registered hooks.
     * Will be initialed in the class constructor.
     */
    protected $_hooks;

    /**
     * @var Enlight_Event_EventManager Instance of the Enlight_Event_EventManager which contains all registered
     * events. Will be initialed in the class constructor.
     */
    protected $_events;

    /**
     * @var Enlight_Plugin_PluginManager Instance of the Enlight_Plugin_PluginManager which contains all
     * registered plugins. Will be initialed in the class constructor.
     */
    protected $_plugins;

    /**
     * @var Enlight_Bootstrap Instance of the application bootstrap. Is generated automatically when accessing the
     * Bootstrap function.
     */
    protected $_bootstrap;

    /**
     * Constructor method.
     *
     * The first argument of the class constructor is the name of the application environment.
     * The second argument is an array of application configurations, an instance of Zend_Config or a config file path.
     * It can contain the application name (options["app"]) and the application path (options["app"]).
     *
     * The application constructor includes the required base classes:
     * Exception, Hook, Singleton, Class, Loader automatically.
     * After the required classes included the application, the Enlight Loader registers
     * the Enlight library namespace and initials the
     * Hook, Event and Plugin manager which stores and manages all
     * registered plugins, events and hooks.
     *
     * If all components are included the Enlight configuration is loaded by the
     * given options parameter.
     * After the configuration is loaded, the application name and path is taken from the config and set in the class properties.
     *
     * @param string $environment
     * @param array $options
     * @param Container $container
     */
    public function __construct($environment, array $options, Container $container)
    {
        self::$instance = $this;
        $this->environment = $environment;
        $this->path = dirname(dirname(__FILE__)) . $this->DS();
        $this->core_path = $this->path . 'Enlight' . $this->DS();

        $this->_loader = $container->get('Loader');

        if (!empty($options['app'])) {
            $this->app = $options['app'];
        } elseif (!isset($this->app)) {
            $this->app = 'Default';
        }
        if (!empty($options['app_path'])) {
            $options['appPath'] = $options['app_path'];
        }
        // Set default path
        if (!isset($this->appPath)) {
            $this->appPath = 'Apps/' . $this->app;
        }
        // Resolve relative path
        $this->appPath = $this->_loader->isReadable($this->appPath);
        $this->appPath = realpath($this->appPath) . $this->DS();

        if (!is_dir($this->appPath)) {
            throw new Exception('App "' . $this->app . '" with path "' . $this->appPath . '" not found failure');
        }

        $this->setOptions($options);

        $this->_hooks    = $container->get('hooks');
        $this->_events   = $container->get('events');
    }

    /**
     * Runs the application bootstrap class to load the specify application resources.
     *
     * @return mixed
     */
    public function run()
    {
        return $this->Bootstrap()->run();
    }

    /**
     * Returns directory separator
     *
     * @return string
     */
    public static function DS()
    {
        return self::$ds;
    }

    /**
     * Returns the base path of the application.
     *
     * @param string $path
     * @return string
     */
    public function Path($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->path . $path . $this->DS();
        }
        return $this->path;
    }

    /**
     * Returns the application path
     *
     * @param string $path
     * @return string
     */
    public function AppPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->appPath . $path . $this->DS();
        }
        return $this->appPath;
    }

    /**
     * Returns vendor path
     *
     * @param string $path
     * @return string
     */
    public function CorePath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->core_path . $path . $this->DS();
        }
        return $this->core_path;
    }

    /**
     * Returns vendor path
     *
     * @param string $path
     * @return string
     */
    public function ComponentsPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->core_path . 'Components' . $this->DS() . $path . $this->DS();
        }
        return $this->core_path . 'Components' . $this->DS();
    }

    /**
     * Returns the name of the application
     *
     * @return string
     */
    public function App()
    {
        return $this->app;
    }

    /**
     * Returns the application environment method
     *
     * @return string
     */
    public function Environment()
    {
        return $this->environment;
    }

    /**
     * Returns the instance of the loader, which is initialed in the class constructor
     *
     * @return Enlight_Loader
     */
    public function Loader()
    {
        return $this->_loader;
    }

    /**
     * Returns the instance of the hook manager, which is initialed in the class constructor
     *
     * @return Enlight_Hook_HookManager
     */
    public function Hooks()
    {
        return $this->_hooks;
    }

    /**
     * Returns the instance of the event manager, which is initialed in the class constructor
     *
     * @return Enlight_Event_EventManager
     */
    public function Events()
    {
        return $this->_events;
    }

    /**
     * Setter function of the _events property.
     * Allows to override the default Shopware Event Manager with an
     * plugin specified event manager.
     * The passed manager has to be an instance of the Enlight_Event_EventManager,
     * otherwise the function throws an exception.
     *
     * @param Enlight_Event_EventManager $manager
     * @throws Exception
     */
    public function setEventManager(Enlight_Event_EventManager $manager)
    {
        if (!$manager instanceof Enlight_Event_EventManager) {
            throw new Exception("The passed event manager isn't an instance of Enlight_Event_EventManager");
        }

        $this->_events = $manager;
    }


    /**
     * Returns the instance of the application bootstrap
     *
     * @return Enlight_Bootstrap
     */
    public function Bootstrap()
    {
        if (!$this->_bootstrap) {
            $class = $this->App() . '_Bootstrap';
            $this->_bootstrap = Enlight_Class::Instance($class, array($this));
        }

        return $this->_bootstrap;
    }

    /**
     * Returns the instance of the application
     *
     * @return Enlight_Application
     */
    public static function Instance()
    {
        return self::$instance;
    }

    /**
     * This method sets the configuration of the options parameter into the different configurations.
     * If the options are not an array, the loadConfig method should be used to convert the options into an array.
     *
     * @deprecated 4.2
     * @param array $options
     * @return Enlight_Application
     */
    public function setOptions(array $options)
    {
        $options = array_change_key_case($options, CASE_LOWER);

        $this->options = $options;

        if (!empty($options['phpsettings'])) {
            $this->setPhpSettings($options['phpsettings']);
        }

        if (!empty($options['includepaths'])) {
            $this->setIncludePaths($options['includepaths']);
        }

        if (!empty($options['autoloadernamespaces'])) {
            foreach ($options['autoloadernamespaces'] as $namespace => $path) {
                if (is_int($namespace)) {
                    $namespace = $path;
                    $path = null;
                }
                $this->_loader->registerNamespace($namespace, $path);
            }
        }

        return $this;
    }

    /**
     * Getter method for the internal options array.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Getter method for a single option addressed by the given key.
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        $options = $this->getOptions();
        $key = strtolower($key);
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Sets the php settings from the config
     *
     * @deprecated 4.2
     * @param array $settings
     * @param string $prefix
     * @return Enlight_Application
     */
    public function setPhpSettings(array $settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if (is_scalar($value)) {
                ini_set($key, $value);
            } elseif (is_array($value)) {
                $this->setPhpSettings($value, $key . '.');
            }
        }
        return $this;
    }

    /**
     * Sets include paths
     *
     * @deprecated 4.2
     * @param array $paths
     * @return Enlight_Application
     */
    public function setIncludePaths(array $paths)
    {
        $this->_loader->setIncludePath($paths);
        return $this;
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
        if (!$this->Bootstrap()->hasResource($name)) {
            throw new Enlight_Exception(
                'Method "' . get_class($this) . '::' . $name . '" not found failure',
                Enlight_Exception::METHOD_NOT_FOUND
            );
        }

        return $this->Bootstrap()->getResource($name);
    }

    /**
     * Returns called resource
     *
     * Example: Enlight_Application::Db()
     *
     * @throws Enlight_Exception
     * @param string $name
     * @param array $value
     * @deprecated 4.2
     * @return mixed
     */
    public static function __callStatic($name, $value = null)
    {
        $enlight = self::Instance();
        if (!$enlight->Bootstrap()->hasResource($name)) {
            throw new Enlight_Exception(
                'Method "' . get_called_class() . '::' . $name . '" not found failure',
                Enlight_Exception::METHOD_NOT_FOUND
            );
        }

        return $enlight->Bootstrap()->getResource($name);
    }
}
