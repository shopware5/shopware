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

use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Implements all methods to register single or multiple controllers and load them automatically.
 *
 * The Enlight_Controller_Dispatcher_Default represents a component
 * to dispatch the request object on the controller. Implements all methods to
 * register single or multiple controllers and load them automatically
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Dispatcher_Default extends Enlight_Controller_Dispatcher
{
    /**
     * @var string contains the current module.
     *             Will be set in the getControllerClass method or in the getControllerPath method.
     *             If the property is set by the getControllerPath method, the string is formatted
     */
    protected $curModule;

    /**
     * Contains the default action for each controller.
     * Will be used in the getActionName function when the passed request instance
     * doesn't contain an action name.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Contains the name of the default controller. Will be used in the dispatch function
     * if the passed request instance doesn't contain an controller name or the
     * request is not dispatchable.
     *
     * @var string
     */
    protected $defaultController = 'index';

    /**
     * Contains the name of the default module. Will be used in the getControllerClass
     * function if the passed request instance doesn't contain an module name and
     * in the addControllerDirectory function if the module name wasn't passed.
     *
     * @var string
     */
    protected $defaultModule = 'frontend';

    /**
     * @var Enlight_Controller_Front contains the instance of the front controller
     */
    protected $frontController;

    /**
     * Holds all valid modules
     * @var array
     */
    protected $modules = ['frontend', 'api', 'widgets', 'backend'];

    /**
     * @var \Shopware\Components\DispatchFormatHelper
     */
    protected $dispatchFormatHelper;

    /**
     * @var array
     */
    private $controllers;

    /**
     * @var Container
     */
    private $container;

    public function __construct(array $controllers, Container $container)
    {
        $this->controllers = $controllers;
        $this->container = $container;
        parent::__construct();
    }

    /**
     * @return \Shopware\Components\DispatchFormatHelper
     */
    public function getDispatchFormatHelper()
    {
        if ($this->dispatchFormatHelper === null) {
            $this->dispatchFormatHelper = Shopware()->Container()->get('shopware.components.dispatch_format_helper');
        }

        return $this->dispatchFormatHelper;
    }

    /**
     * Returns the formatted controller name. Removes all '_' .
     *
     * @param string $unFormatted
     *
     * @return mixed
     */
    public function formatControllerName($unFormatted)
    {
        $dispatchFormatHelper = $this->getDispatchFormatHelper();

        return str_replace('_', '', $dispatchFormatHelper->formatNameForDispatch($unFormatted));
    }

    /**
     * Returns the formatted action name. Removes all '_' .
     *
     * @param string $unFormatted
     *
     * @return mixed
     */
    public function formatActionName($unFormatted)
    {
        $dispatchFormatHelper = $this->getDispatchFormatHelper();

        return str_replace('_', '', $dispatchFormatHelper->formatNameForDispatch($unFormatted));
    }

    /**
     * Returns the formatted module name. Upper case the first character of the module name.
     *
     * @param string $unFormatted
     *
     * @return string
     */
    public function formatModuleName($unFormatted)
    {
        $dispatchFormatHelper = $this->getDispatchFormatHelper();

        return ucfirst($dispatchFormatHelper->formatNameForDispatch($unFormatted));
    }

    /**
     * Sets the default controller name.
     *
     * @param string $controller
     *
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultControllerName($controller)
    {
        $this->defaultController = (string) $controller;

        return $this;
    }

    /**
     * Returns the default controller name.
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return $this->defaultController;
    }

    /**
     * Sets the default action name.
     *
     * @param string $action
     *
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultAction($action)
    {
        $this->defaultAction = (string) $action;

        return $this;
    }

    /**
     * Returns the default action name.
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * Sets the default module name.
     *
     * @param string $module
     *
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultModule($module)
    {
        $this->defaultModule = (string) $module;

        return $this;
    }

    /**
     * Returns the default module name
     *
     * @return string
     */
    public function getDefaultModule()
    {
        return $this->defaultModule;
    }

    /**
     * Returns the controller class of the given request class. The class name is imploded by '_'
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return array|string
     */
    public function getControllerClass(Enlight_Controller_Request_Request $request)
    {
        if (!$request->getControllerName()) {
            $request->setControllerName($this->defaultController);
        }

        if (!$request->getModuleName()) {
            $request->setModuleName($this->defaultModule);
        }

        $module = $request->getModuleName();
        $this->curModule = $module;

        $moduleName = $this->formatModuleName($module);
        $controllerName = $this->formatControllerName($request->getControllerName());

        $class = ['Shopware', 'Controllers', $moduleName, $controllerName];
        $class = implode('_', $class);

        return $class;
    }

    /**
     * Returns the controller path of the given request class.
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return string
     */
    public function getControllerPath(Enlight_Controller_Request_Request $request)
    {
        $controllerName = $request->getControllerName();
        $controllerName = $this->formatControllerName($controllerName);
        $moduleName = $this->formatModuleName($this->curModule);
        $controllerId = $this->getControllerServiceId($moduleName, $controllerName);
        $request->unsetAttribute('controllerId');

        if ($event = Shopware()->Events()->notifyUntil(
                'Enlight_Controller_Dispatcher_ControllerPath_' . $moduleName . '_' . $controllerName,
                ['subject' => $this, 'request' => $request]
                )
        ) {
            return $event->getReturn();
        }

        if ($controllerId) {
            $request->setAttribute('controllerId', $controllerId);
            return clone $this->container->get($controllerId);
        }

        return null;
    }

    /**
     * Returns the action method of the given request class.
     * If no action name is set in the request class, the default action is used.
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return string
     */
    public function getActionMethod(Enlight_Controller_Request_Request $request)
    {
        $action = $request->getActionName();
        if (empty($action)) {
            $action = $this->getDefaultAction();
            $request->setActionName($action);
        }
        $formatted = $this->formatActionName($action);
        $formatted = strtolower(substr($formatted, 0, 1)) . substr($formatted, 1) . 'Action';

        return $formatted;
    }

    /**
     * Returns the full path of the controller name by the given request class.
     * To generate the full controller path the module and controller name must be set in the given request object.
     * The module and controller path is imploded by '_'
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return string
     */
    public function getFullControllerName(Enlight_Controller_Request_Request $request)
    {
        $parts = [
            $this->formatModuleName($request->getModuleName()),
            $this->formatControllerName($request->getControllerName()),
        ];

        return implode('_', $parts);
    }

    /**
     * Returns the full path of the action name.
     * To generate the full action path the module, controller and action name must be set in the given request object.
     * The module, controller and action path is imploded by '_'.
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return string
     */
    public function getFullActionName(Enlight_Controller_Request_Request $request)
    {
        $parts = [
            $this->formatModuleName($request->getModuleName()),
            $this->formatControllerName($request->getControllerName()),
            $this->formatActionName($request->getActionName()),
        ];

        return implode('_', $parts);
    }

    /**
     * Returns whether the given request object is dispatchable.
     * Checks first if the controller class of the request object exists.
     * If the controller class exists, the enlight loader class checks if the controller path is readable.
     *
     * @param Enlight_Controller_Request_Request $request
     *
     * @return bool|string
     */
    public function isDispatchable(Enlight_Controller_Request_Request $request)
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return false;
        }

        if ($this->isForbiddenController($className)) {
            return false;
        }

        if (class_exists($className, false)) {
            return true;
        }

        $path = $this->getControllerPath($request);

        if ($path === null) {
            return false;
        }

        return is_object($path) || class_exists($path) || Enlight_Loader::isReadable($path);
    }

    /**
     * Checks if a controller directory exists for the given module.
     *
     * @param string $module
     *
     * @return bool
     */
    public function isValidModule($module)
    {
        if (!is_string($module)) {
            return false;
        }

        return in_array(strtolower($module), $this->modules);
    }

    public function setModules(array $modules): void
    {
        $this->modules = $modules;
    }

    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * If the given request is not dispatchable, the default controller is set.
     * Then it tries to load the controller class and appends the hook proxies.
     * If the hook proxies are added, the dispatched flag of the request object is set to true.
     * If the disableOutputBuffering parameter isn't set, the output buffering starts.
     * After that, run the dispatch on the controller.
     * At the ending the body is added to the response object.
     *
     * @param Enlight_Controller_Request_Request   $request
     * @param Enlight_Controller_Response_Response $response
     *
     * @throws Enlight_Controller_Exception|Enlight_Exception|Exception
     */
    public function dispatch(Enlight_Controller_Request_Request $request,
                             Enlight_Controller_Response_Response $response
    ) {
        $this->setResponse($response);

        if (!$this->isDispatchable($request)) {
            throw new Enlight_Controller_Exception(
                'Controller "' . $request->getControllerName() . '" not found for request url ' . $request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri(),
                Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found
            );
        }

        $class = $this->getControllerClass($request);
        $path = $this->getControllerPath($request);

        if (is_object($path) || class_exists($path)) {
            $class = $path;
            $path = null;
        }

        if (!is_object($class)) {
            try {
                Shopware()->Loader()->loadClass($class, $path);
            } catch (Exception $e) {
                throw new Enlight_Exception('Controller "' . $class . '" can\'t load failure');
            }

            $proxy = Shopware()->Hooks()->getProxy($class);

            /** @var Enlight_Controller_Action $controller */
            $controller = new $proxy();
        } else {
            /** @var Enlight_Controller_Action $controller */
            $controller = $class;
        }

        $controller->initController($request, $response);

        $controller->setFront($this->Front());

        if ($controller instanceof ContainerAwareInterface) {
            $container = Shopware()->Container();
            $controller->setContainer($container);
        }

        $action = $this->getActionMethod($request);

        $request->setDispatched(true);

        $disableOb = $this->Front()->getParam('disableOutputBuffering');
        $obLevel = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $controller->dispatch($action);
        } catch (Exception $e) {
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }
    }

    private function getControllerServiceId(string $module, string $name): ?string
    {
        $controllerKey = strtolower(sprintf('%s_%s', $module, $name));

        return isset($this->controllers[$controllerKey]) ? $this->controllers[$controllerKey] : null;
    }

    private function isForbiddenController(string $className): bool
    {
        return in_array($className, $this->container->getParameter('shopware.controller.blacklisted_controllers'), true);
    }
}
