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
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Test case for Enlight controller.
 *
 * The Enlight_Components_Test_Controller_TestCase extends the basic Enlight_Components_Test_TestCase
 * with controller specified functions to grant an easily access to standard controller actions.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_Controller_TestCase extends Enlight_Components_Test_TestCase
{
    /**
     * Instance of the Front resource
     * @var Enlight_Controller_Front
     */
    protected $_front;

    /**
     * Instance of the View resource
     * @var Enlight_Template_Manager
     */
    protected $_template;

    /**
     * Instance of the enlight view. Is filled in the dispatch function with the template.
     * @var Enlight_View_Default
     */
    protected $_view;

    /**
     * Instance of the enlight request. Filled in the dispatch function.
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Instance of the enlight response. Filled in the dispatch function.
     * @var Zend_Controller_Response_Http
     */
    protected $_response;

    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        $app = Enlight_Application::Instance();
        $app->Bootstrap()
                ->resetResource('Session')
                ->resetResource('Auth');

        $this->reset();
    }

    /**
     * Dispatch the request
     *
     * @param   string|null $url
     * @return  Zend_Controller_Response_Abstract
     */
    public function dispatch($url = null)
    {
        $request = $this->Request();
        if (null !== $url) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);

        $response = $this->Response();

        $front = $this->Front()
                ->setRequest($request)
                ->setResponse($response);
        $front->dispatch();

        /** @var $viewRenderer Enlight_Controller_Plugins_ViewRenderer_Bootstrap */
        $viewRenderer = $front->Plugins()->get('ViewRenderer');
        $this->_view = $viewRenderer->Action()->View();

        /*
        if (!$front->getParam('noErrorHandler')) {
            $front->Plugins()->load('ErrorHandler');
        }
        if (!$front->getParam('noViewRenderer')) {
            $front->Plugins()->load('ViewRenderer');
        }

        $dispatcher = $front->Dispatcher();

        $dispatcher->dispatch($request, $response);
        */
        return $response;
    }

    /**
     * Reset all instances, resources and init the internal view, template and front properties
     */
    public function reset()
    {
        $app = Enlight_Application::Instance();

        $this->resetRequest();
        $this->resetResponse();

        // Force the assignments to be cleared. Needed for some test cases
        if ($this->_view && $this->_view->hasTemplate()) {
            $this->_view->clearAssign();
        }

        $this->_view = null;
        $this->_template = null;
        $this->_front = null;

        $app->Plugins()->reset();
        //$app->Hooks()->resetHooks();
        $app->Events()->reset();
        //$app->Db()->getProfiler()->clear();

        $app->Bootstrap()
                ->resetResource('Plugins')
                ->resetResource('Front')
                ->resetResource('Router')
//            ->resetResource('Template')
//            ->resetResource('Snippets')
                ->resetResource('System')
                ->resetResource('Modules')
                ->resetResource('Models');
//            ->resetResource('Config')
//            ->resetResource('Shop');
//            ->resetResource('Session')
//            ->resetResource('Auth');

        $app->Bootstrap()->loadResource('Front');
        $app->Bootstrap()->loadResource('Plugins');
    }

    /**
     * Reset the request object
     *
     * @return Enlight_Components_Test_Controller_TestCase
     */
    public function resetRequest()
    {
        if ($this->_request instanceof Zend_Controller_Request_Http) {
            $this->_request->clearQuery()
                    ->clearPost()
                    ->clearCookies();
        }
        $this->_request = null;
        return $this;
    }

    /**
     * Reset the response object
     *
     * @return Enlight_Components_Test_Controller_TestCase
     */
    public function resetResponse()
    {
        $this->_response = null;
        return $this;
    }

    /**
     * Retrieve front controller instance
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        if (null === $this->_front) {
            $this->_front = Enlight_Application::Instance()->Bootstrap()->getResource('Front');
        }
        return $this->_front;
    }

    /**
     * Retrieve template instance
     *
     * @return Enlight_Template_Manager
     */
    public function Template()
    {
        if (null === $this->_template) {
            $this->_template = Enlight_Application::Instance()->Bootstrap()->getResource('Template');
        }
        return $this->_template;
    }

    /**
     * Retrieve view instance
     *
     * @return Enlight_View_Default
     */
    public function View()
    {
        return $this->_view;
    }

    /**
     * Retrieve test case request object
     *
     * @return Enlight_Controller_Request_RequestTestCase
     */
    public function Request()
    {
        if (null === $this->_request) {
            $this->_request = new Enlight_Controller_Request_RequestTestCase;
        }
        return $this->_request;
    }

    /**
     * Retrieve test case response object
     *
     * @return Enlight_Controller_Response_ResponseHttp
     */
    public function Response()
    {
        if (null === $this->_response) {
            $this->_response = new Enlight_Controller_Response_ResponseTestCase;
        }
        return $this->_response;
    }

    /**
     * Magic get method
     *
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'request':
                return $this->Request();
            case 'response':
                return $this->Response();
            case 'front':
            case 'frontController':
                return $this->Front();
        }
        return null;
    }
}
