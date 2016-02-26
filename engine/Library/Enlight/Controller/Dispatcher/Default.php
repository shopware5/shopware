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
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

use Shopware\Components\DependencyInjection\ContainerAwareInterface;

/**
 * Implements all methods to register single or multiple controllers and load them automatically.
 *
 * The Enlight_Controller_Dispatcher_Default represents a component
 * to dispatch the request object on the controller. Implements all methods to
 * register single or multiple controllers and load them automatically
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Dispatcher_Default extends Enlight_Controller_Dispatcher
{
    /**
     * @var string Current directory of the controller.
     * Will be set in the getControllerClass method or in the getControllerPath method.
     */
    protected $curDirectory;

    /**
     * @var string Contains the current module.
     * Will be set in the getControllerClass method or in the getControllerPath method.
     * If the property is set by the getControllerPath method, the string is formatted.
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
     * @var Zend_Controller_Front Contains the instance of the front controller.
     */
    protected $frontController;

    /**
     * @var string Contains the path delimiter character used to format action, controller and module names.
     */
    protected $pathDelimiter = '_';

    /**
     * @var array Contains the word delimiter characters used to format action, controller and module names.
     */
    protected $wordDelimiter = array('-', '.');

    /**
     * @var array Contains all added controller directories. Used to get the controller
     * directory of a module
     */
    protected $controllerDirectory = array();

    /**
     * Adds a controller directory. If no module is given, the default module will be used.
     *
     * @param      $path
     * @param null $module
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function addControllerDirectory($path, $module = null)
    {
        if (empty($module)) {
            $module = $this->defaultModule;
        }

        $module = $this->formatModuleName($module);
        $path = realpath($path) . '/';

        $this->controllerDirectory[$module] = $path;

        return $this;
    }

    /**
     * Sets the controller directory. The directory can be given as an array or a string.
     *
     * @param string|array $directory
     * @param string|null  $module
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setControllerDirectory($directory, $module = null)
    {
        $this->controllerDirectory = array();

        if (is_string($directory)) {
            $this->addControllerDirectory($directory, $module);
        } else {
            foreach ((array) $directory as $module => $path) {
                $this->addControllerDirectory($path, $module);
            }
        }

        return $this;
    }

    /**
     * Returns the controller directory.
     * If more than one directory exists the function returns the controller directory of the given module.
     * If no module name is passed, the function returns the whole controller directory array.
     * @param null $module
     * @return array|null
     */
    public function getControllerDirectory($module = null)
    {
        if ($module === null) {
            return $this->controllerDirectory;
        }
        $module = $this->formatModuleName($module);
        if (isset($this->controllerDirectory[$module])) {
            return $this->controllerDirectory[$module];
        } else {
            return null;
        }
    }

    /**
     * Removes the controller directory for the given module.
     * @param $module
     * @return bool
     */
    public function removeControllerDirectory($module)
    {
        $module = (string) $module;
        if (isset($this->controllerDirectory[$module])) {
            unset($this->controllerDirectory[$module]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds the given path to the module directory
     * @param $path
     * @return Enlight_Controller_Dispatcher_Default
     * @throws Enlight_Controller_Exception
     */
    public function addModuleDirectory($path)
    {
        try {
            $dir = new DirectoryIterator($path);
        } catch (Exception $e) {
            throw new Enlight_Controller_Exception("Directory $path not readable", 0, $e);
        }

        foreach ($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $module = $file->getFilename();

            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
                continue;
            }

            $moduleDir = $file->getPathname();
            $this->addControllerDirectory($moduleDir, $module);
        }

        return $this;
    }

    /**
     * Returns the formatted controller name. Removes all '_' .
     * @param $unFormatted
     * @return mixed
     */
    public function formatControllerName($unFormatted)
    {
        return str_replace('_', '', $this->formatName($unFormatted));
    }

    /**
     * Returns the formatted action name. Removes all '_' .
     * @param $unFormatted
     * @return mixed
     */
    public function formatActionName($unFormatted)
    {
        return str_replace('_', '', $this->formatName($unFormatted));
    }

    /**
     * Returns the formatted module name. Upper case the first character of the module name.
     * @param $unFormatted
     * @return string
     */
    public function formatModuleName($unFormatted)
    {
        return ucfirst($this->formatName($unFormatted));
    }

    /**
     * Internal helper function to format action, controller and module names.
     *
     * @param      $unFormatted
     * @param bool $isAction
     * @return string
     */
    protected function formatName($unFormatted, $isAction = false)
    {
        if (!$isAction) {
            $segments = explode($this->pathDelimiter, $unFormatted);
        } else {
            $segments = (array) $unFormatted;
        }

        foreach ($segments as $key => $segment) {
            $segment = preg_replace('#[A-Z]#', ' $0', $segment);
            $segment = str_replace($this->wordDelimiter, ' ', strtolower($segment));
            $segment = preg_replace('/[^a-z0-9 ]/', '', $segment);
            $segments[$key] = str_replace(' ', '', ucwords($segment));
        }

        return implode('_', $segments);
    }

    /**
     * Sets the default controller name.
     * @param $controller
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultControllerName($controller)
    {
        $this->defaultController = (string) $controller;
        return $this;
    }

    /**
     * Returns the default controller name.
     * @return string
     */
    public function getDefaultControllerName()
    {
        return $this->defaultController;
    }

    /**
     * Sets the default action name.
     * @param $action
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultAction($action)
    {
        $this->defaultAction = (string) $action;
        return $this;
    }

    /**
     * Returns the default action name.
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * Sets the default module name.
     * @param $module
     * @return Enlight_Controller_Dispatcher_Default
     */
    public function setDefaultModule($module)
    {
        $this->defaultModule = (string) $module;
        return $this;
    }

    /**
     * Returns the default module name
     * @return string
     */
    public function getDefaultModule()
    {
        return $this->defaultModule;
    }

    /**
     * Returns the controller class of the given request class. The class name is imploded by '_'
     * @param   Enlight_Controller_Request_Request $request
     * @return  array|string
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
        $this->curDirectory = $this->getControllerDirectory($module);

        $moduleName = $this->formatModuleName($module);
        $controllerName = $this->formatControllerName($request->getControllerName());

        $class = array(Enlight_Application::Instance()->App(), 'Controllers', $moduleName, $controllerName);
        $class = implode('_', $class);
        return $class;
    }

    /**
     * Returns the controller path of the given request class.
     *
     * @param   Enlight_Controller_Request_Request $request
     * @return  string
     */
    public function getControllerPath(Enlight_Controller_Request_Request $request)
    {
        $controllerName = $request->getControllerName();
        $controllerName = $this->formatControllerName($controllerName);
        $moduleName = $this->formatModuleName($this->curModule);
        if ($event = Enlight_Application::Instance()->Events()->notifyUntil(
                'Enlight_Controller_Dispatcher_ControllerPath_' . $moduleName . '_' . $controllerName,
                array('subject' => $this, 'request' => $request)
                )
        ) {
            $path = $event->getReturn();
        } else {
            $path = $this->curDirectory . $controllerName . '.php';
        }
        return $path;
    }

    /**
     * Returns the action method of the given request class.
     * If no action name is set in the request class, the default action is used.
     * @param   Enlight_Controller_Request_Request $request
     * @return  string
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
     * @return string
     */
    public function getFullControllerName(Enlight_Controller_Request_Request $request)
    {
        $parts = array(
            $this->formatModuleName($request->getModuleName()),
            $this->formatControllerName($request->getControllerName())
        );
        return implode('_', $parts);
    }

    /**
     * Returns the full path of the action name.
     * To generate the full action path the module, controller and action name must be set in the given request object.
     * The module, controller and action path is imploded by '_'.
     *
     * @param Enlight_Controller_Request_Request $request
     * @return string
     */
    public function getFullActionName(Enlight_Controller_Request_Request $request)
    {
        $parts = array(
            $this->formatModuleName($request->getModuleName()),
            $this->formatControllerName($request->getControllerName()),
            $this->formatActionName($request->getActionName())
        );
        return implode('_', $parts);
    }

    /**
     * Returns whether the given request object is dispatchable.
     * Checks first if the controller class of the request object exists.
     * If the controller class exists, the enlight loader class checks if the controller path is readable.
     *
     * @param Enlight_Controller_Request_Request $request
     * @return bool|string
     */
    public function isDispatchable(Enlight_Controller_Request_Request $request)
    {
        $className = $this->getControllerClass($request);
        if (!$className) {
            return false;
        }
        if (class_exists($className, false)) {
            return true;
        }
        $path = $this->getControllerPath($request);
        return Enlight_Loader::isReadable($path);
    }

    /**
     * Checks if a controller directory exists for the given module.
     *
     * @param $module
     * @return bool
     */
    public function isValidModule($module)
    {
        if (!is_string($module)) {
            return false;
        }

        $controllerDir = $this->getControllerDirectory($module);

        return !empty($controllerDir);
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
     * @throws Enlight_Controller_Exception|Enlight_Exception|Exception
     */
    public function dispatch(Enlight_Controller_Request_Request $request,
                             Enlight_Controller_Response_Response $response
    ) {
        $this->setResponse($response);

        if (!$this->isDispatchable($request)) {
            throw new Enlight_Controller_Exception(
                'Controller "' . $request->getControllerName() . '" not found',
                Enlight_Controller_Exception::Controller_Dispatcher_Controller_Not_Found
            );
        }

        $class = $this->getControllerClass($request);
        $path = $this->getControllerPath($request);

        try {
            Enlight_Application::Instance()->Loader()->loadClass($class, $path);
        } catch (Exception $e) {
            throw new Enlight_Exception('Controller "' . $class . '" can\'t load failure');
        }

        $proxy = Enlight_Application::Instance()->Hooks()->getProxy($class);

        /** @var $controller Enlight_Controller_Action */
        $controller = new $proxy($request, $response);
        $controller->setFront($this->Front());

        if ($controller instanceof ContainerAwareInterface) {
            $container = Enlight_Application::Instance()->Container();
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
}
