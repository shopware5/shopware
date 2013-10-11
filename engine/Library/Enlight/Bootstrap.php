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

/**
 * The Enlight_Bootstrap is responsible to manage the application resources.
 *
 * To load the resources the bootstrap uses the application configuration which could
 * contain the database connection data.
 *
 * @category   Enlight
 * @package    Enlight_Application
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Bootstrap extends Enlight_Class implements Enlight_Hook
{
    /**
     * Constant for the bootstrap status, set before the resource is initialed
     */
    const STATUS_BOOTSTRAP = 0;

    /**
     * Constant for the bootstrap status, set after the resource is successfully initialed
     */
    const STATUS_LOADED = 1;

    /**
     * Constant for the bootstrap status, set if an exception is thrown by the initialisation
     */
    const STATUS_NOT_FOUND = 2;

    /**
     * Constant for the bootstrap status, set when the resource is registered.
     */
    const STATUS_ASSIGNED = 3;

    /**
     * Property which contains all registered resources
     *
     * @var array
     */
    protected $resourceList = array();

    /**
     * Property which contains all states for the registered resources.
     *
     * @var array
     */
    protected $resourceStatus = array();

    /**
     * @var Enlight_Components_ResourceLoader
     */
    protected $resourceLoader;

    /**
     * Instance of the enlight application.
     *
     * @var Enlight_Application
     */
    protected $application;

    /**
     * The class constructor sets the instance of the given enlight application into
     * the internal $application property.
     *
     * @param Enlight_Application $application
     */
    public function __construct(Enlight_Application $application)
    {
        $this->setApplication($application);

        $this->resourceLoader = $application->ResourceLoader();

        parent::__construct();
    }

    /**
     * Sets the application instance into the internal $application property.
     *
     * @param  Enlight_Application $application
     * @return Enlight_Bootstrap
     */
    public function setApplication(Enlight_Application $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * Returns the application instance.
     *
     * @return Enlight_Application
     */
    public function Application()
    {
        return $this->application;
    }

    /**
     * The run method loads the Enlight_Controller_Front resource and runs the dispatch function.
     *
     * @return mixed
     */
    public function run()
    {
        /** @var $front Enlight_Controller_Front */
        $front = $this->getResource('Front');

        return $front->dispatch();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasInit($name)
    {
        return method_exists($this, $this->buildInitName($name));
    }

    /**
     * Wrapper function to call the protected init*-Functions.
     * Only $caller-instances of Enlight_Components_ResourceLoader is allowed to call.
     *
     * @param string $name
     * @param $caller
     * @return mixed
     * @throws
     */
    public function callInit($name, $caller)
    {
        if (!($caller instanceof Enlight_Components_ResourceLoader)) {
            throw \Exception("TODO");
        }

        $methodName = $this->buildInitName($name);

        return $this->$methodName();
    }

    /**
     * @param $name
     * @return string
     */
    protected function buildInitName($name)
    {
        return 'init' . ucfirst($name);
    }

    /**
     * Loads the Zend resource and initials the Enlight_Controller_Front class.
     * After the front resource is loaded, the controller path is added to the
     * front dispatcher. After the controller path is set to the dispatcher,
     * the plugin namespace of the front resource is set.
     *
     * @return Enlight_Controller_Front
     */
    protected function initFront()
    {
        $this->loadResource('Zend');

        /** @var $front Enlight_Controller_Front */
        $front = Enlight_Class::Instance('Enlight_Controller_Front');

        $front->Dispatcher()->addModuleDirectory(
            $this->Application()->AppPath('Controllers')
        );

        $config = $this->Application()->getOption('Front');
        if ($config !== null) {
            $front->setParams($config);
        }

        /** @var $plugins  Enlight_Plugin_PluginManager */
        $plugins = $this->getResource('Plugins');
        $plugins->registerNamespace($front->Plugins());

        $front->setParam('bootstrap', $this);

        if (!empty($config['throwExceptions'])) {
            $front->throwExceptions(true);
        }
        if (!empty($config['returnResponse'])) {
            $front->returnResponse(true);
        }

        return $front;
    }

    /**
     * The init template method instantiates the Enlight_Template_Manager
     * and sets the cache, template and compile directory into the manager.
     * After the directories has been set, the template configuration is set
     * by the internal config array.
     *
     * @return Enlight_Template_Manager
     */
    protected function initTemplate()
    {
        /** @var $template Enlight_Template_Manager */
        $template = Enlight_Class::Instance('Enlight_Template_Manager');

        $template->setCompileDir($this->Application()->AppPath('Cache_Compiles'));
        $template->setCacheDir($this->Application()->AppPath('Cache_Templates'));
        $template->setTemplateDir($this->Application()->AppPath('Views'));

        $config = $this->Application()->getOption('template');
        $template->setOptions($config);

        return $template;
    }

    /**
     * Registers the Zend namespace.
     *
     * @return bool
     */
    protected function initZend()
    {
        $this->Application()->Loader()->registerNamespace('Zend', 'Zend/');

        return true;
    }

    /**
     * Adds the given resource to the internal resource list and sets the STATUS_ASSIGNED status.
     * The given name will be used as identifier.
     *
     * @param string $name
     * @param mixed $resource
     * @return Enlight_Bootstrap
     */
    public function registerResource($name, $resource)
    {
        return $this->resourceLoader->registerResource($name, $resource);
    }

    /**
     * Checks if the given resource name is already registered. If not the resource is loaded.
     *
     * @param string $name
     * @return bool
     */
    public function hasResource($name)
    {
        return $this->resourceLoader->hasResource($name);
    }

    /**
     * Checks if the given resource name is already registered.
     * Unlike as the hasResource method is, if the resource does not exist the resource will not even loaded.
     *
     * @param string $name
     * @return bool
     */
    public function issetResource($name)
    {
        return $this->resourceLoader->issetResource($name);
    }

    /**
     * Getter method for a single resource. If the source is not already registered, this function will
     * load the resource automatically. In case the resource is not found the status STATUS_NOT_FOUND is
     * set and an Enlight_Exception is thrown.
     *
     * @param string $name
     * @return mixed
     */
    public function getResource($name)
    {
        return $this->resourceLoader->getResource($name);
    }

    /**
     * Loads the given resource. If the resource is already registered and the status
     * is STATUS_BOOTSTRAP an Enlight_Exception is thrown.
     * The resource is initial by the Enlight_Bootstrap_InitResource event.
     * If this event doesn't exist for the given resource, the resource is initialed
     * by call_user_func.
     * After the resource is initialed the event Enlight_Bootstrap_AfterInitResource is
     * fired. In case an exception is thrown by initializing the resource,
     * Enlight sets the status STATUS_NOT_FOUND for the resource in the resource status list.
     * In case the resource successfully initialed the resource has the status STATUS_LOADED
     *
     * @param string $name
     * @return bool
     */
    public function loadResource($name)
    {
        return $this->resourceLoader->loadResource($name);
    }

    /**
     * If the given resource is set, the resource and the resource status are removed from the
     * list properties.
     *
     * @param string $name
     * @return Enlight_Bootstrap
     */
    public function resetResource($name)
    {
        return $this->resourceLoader->resetResource($name);
    }

    /**
     * Returns called resource
     *
     * @param string $name
     * @param array $arguments
     * @return Enlight_Class Resource
     */
    public function __call($name, $arguments = null)
    {
        return $this->resourceLoader->getResource($name);
    }
}
