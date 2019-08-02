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

use Shopware\Components\Cart\ConditionalLineItemServiceInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test case for Enlight controller.
 *
 * The Enlight_Components_Test_Controller_TestCase extends the basic Enlight_Components_Test_TestCase
 * with controller specified functions to grant an easily access to standard controller actions.
 *
 *
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_Controller_TestCase extends Enlight_Components_Test_TestCase
{
    /**
     * Instance of the Front resource
     *
     * @var Enlight_Controller_Front
     */
    protected $_front;

    /**
     * Instance of the View resource
     *
     * @var Enlight_Template_Manager
     */
    protected $_template;

    /**
     * Instance of the enlight view. Is filled in the dispatch function with the template.
     *
     * @var Enlight_View_Default
     */
    protected $_view;

    /**
     * Instance of the enlight request. Filled in the dispatch function.
     *
     * @var Enlight_Controller_Request_Request
     */
    protected $_request;

    /**
     * Instance of the enlight response. Filled in the dispatch function.
     *
     * @var Enlight_Controller_Response_Response
     */
    protected $_response;

    /**
     * Magic get method
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

    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        Shopware()->Container()->reset('session');
        Shopware()->Container()->reset('auth');

        $this->reset();
    }

    /**
     * Dispatch the request
     *
     * @param string|null $url
     * @param bool        $followRedirects
     *
     * @return Enlight_Controller_Response_Response
     */
    public function dispatch($url = null, $followRedirects = false)
    {
        $request = $this->Request();
        if ($url !== null) {
            $request->setRequestUri($url);
        }
        $request->setPathInfo(null);

        $response = $this->Response();

        $front = $this->Front()
                ->setRequest($request)
                ->setResponse($response);

        $front->dispatch();

        if ($followRedirects && $this->Response()->getStatusCode() === Response::HTTP_FOUND) {
            $link = parse_url($this->Response()->getHeader('Location'), PHP_URL_PATH);
            $this->resetResponse();
            $cookies = $this->Response()->getCookies();
            $this->resetRequest();
            $this->Request()->setCookies($cookies);

            return $this->dispatch($link);
        }

        /** @var Enlight_Controller_Plugins_ViewRenderer_Bootstrap $viewRenderer */
        $viewRenderer = $front->Plugins()->get('ViewRenderer');
        $this->_view = $viewRenderer->Action()->View();

        return $response;
    }

    /**
     * Reset all instances, resources and init the internal view, template and front properties
     */
    public function reset()
    {
        $app = Shopware();

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
        $app->Events()->reset();

        $container = Shopware()->Container();

        $container->get('models')->clear();

        $container
            ->reset('plugins')
            ->reset('front')
            ->reset('router')
            ->reset('system')
            ->reset('modules')
            ->reset(ConditionalLineItemServiceInterface::class);

        $container->load('front');
        $container->load('plugins');

        foreach ($container->get('kernel')->getPlugins() as $plugin) {
            if (!$plugin->isActive()) {
                continue;
            }
            $container->get('events')->addSubscriber($plugin);
        }
    }

    /**
     * Reset the request object
     *
     * @return Enlight_Components_Test_Controller_TestCase
     */
    public function resetRequest()
    {
        if ($this->_request instanceof Enlight_Controller_Request_RequestTestCase) {
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
        if ($this->_front === null) {
            $this->_front = Shopware()->Container()->get('front');
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
        if ($this->_template === null) {
            $this->_template = Shopware()->Container()->get('template');
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
        if ($this->_request === null) {
            $this->_request = Enlight_Controller_Request_RequestTestCase::createFromGlobals();
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
        if ($this->_response === null) {
            $this->_response = new Enlight_Controller_Response_ResponseTestCase();
        }

        return $this->_response;
    }

    /**
     * Allows to set a Shopware config
     *
     * @param string $name
     */
    protected function setConfig($name, $value)
    {
        Shopware()->Container()->get('config_writer')->save($name, $value);
        Shopware()->Container()->get('cache')->clean();
        Shopware()->Container()->get('config')->setShop(Shopware()->Shop());
    }
}
