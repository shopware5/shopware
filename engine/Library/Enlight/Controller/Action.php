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

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Form\Form;

/**
 * Basic class for each Enlight controller action.
 *
 * The Enlight_Controller_Action is the basic class for the specified controllers. It is responsible
 * for the data access. After the dispatcher is dispatched the controller Enlight_Controller_Action
 * takes care, that the right action is executed.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Controller_Action extends Enlight_Class implements Enlight_Hook, ContainerAwareInterface
{
    /**
     * @var Enlight_Controller_Front
     */
    protected $front;

    /**
     * @var Enlight_View_Default
     */
    protected $view;

    /**
     * Will be set in the class constructor. Passed to the class init and controller init function.
     * Required for the forward, dispatch and redirect functions.
     *
     * @var Enlight_Controller_Request_RequestHttp
     */
    protected $request;

    /**
     * Will be set in the class constructor. Passed to the class init and controller init function.
     * Required for the forward, dispatch and redirect functions.
     *
     * @var Enlight_Controller_Response_ResponseHttp
     */
    protected $response;

    /**
     * @var Shopware\Components\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var string Contains the name of the controller
     */
    protected $controller_name;

    /**
     * Override default Enlight constructor
     */
    public function __construct()
    {
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp   $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     *
     * @throws \Exception
     * @throws \Enlight_Exception
     * @throws \Enlight_Event_Exception
     */
    public function initController(Enlight_Controller_Request_RequestHttp $request,
                                Enlight_Controller_Response_ResponseHttp $response
    ) {
        $this->setRequest($request)->setResponse($response);

        $this->controller_name = $this->Front()->Dispatcher()->getFullControllerName($this->Request());

        Shopware()->Events()->notify(
            __CLASS__ . '_Init',
            ['subject' => $this, 'request' => $this->Request(), 'response' => $this->Response()]
        );
        Shopware()->Events()->notify(
            __CLASS__ . '_Init_' . $this->controller_name,
            ['subject' => $this, 'request' => $this->Request(), 'response' => $this->Response()]
        );

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * Magic caller method
     *
     * @param string $name
     * @param array  $value
     *
     * @throws \Enlight_Exception
     * @throws Enlight_Controller_Exception
     *
     * @return mixed
     */
    public function __call($name, $value = null)
    {
        if (substr($name, -6) === 'Action') {
            throw new Enlight_Controller_Exception(
                'Action "' . $this->controller_name . '_' . $name . '" not found failure for request url ' . $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getRequestUri(),
                Enlight_Controller_Exception::ActionNotFound
            );
        }

        return parent::__call($name, $value);
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
    }

    /**
     * Post dispatch method
     */
    public function postDispatch()
    {
    }

    /**
     * Dispatch action method.
     * After the pre dispatch event notified the internal post dispatch event will executed.
     * After the internal post dispatch executed the post dispatch event is notify.
     *
     * @param string $action
     *
     * @throws \Exception
     * @throws \Enlight_Exception
     * @throws \Enlight_Event_Exception
     */
    public function dispatch($action)
    {
        $args = new Enlight_Controller_ActionEventArgs([
            'subject' => $this,
            'request' => $this->Request(),
            'response' => $this->Response(),
        ]);

        $moduleName = ucfirst($this->Request()->getModuleName());

        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch',
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch_' . $moduleName,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PreDispatch_' . $this->controller_name,
            $args
        );

        $this->preDispatch();

        if ($this->Request()->isDispatched() && !$this->Response()->isRedirect()) {
            $action_name = $this->Front()->Dispatcher()->getFullActionName($this->Request());
            if (!$event = Shopware()->Events()->notifyUntil(
                __CLASS__ . '_' . $action_name,
                ['subject' => $this]
            )
            ) {
                $this->$action(...$this->getActionArguments($action));
            }
            $this->postDispatch();
        }

        // Fire "Secure"-PostDispatch-Events only if:
        // - Request is Dispatched
        // - Response in no Exception
        // - View has template
        if ($this->Request()->isDispatched()
            && !$this->Response()->isException()
            && $this->View()->hasTemplate()
        ) {
            Shopware()->Events()->notify(
                __CLASS__ . '_PostDispatchSecure_' . $this->controller_name,
                $args
            );

            Shopware()->Events()->notify(
                __CLASS__ . '_PostDispatchSecure_' . $moduleName,
                $args
            );

            Shopware()->Events()->notify(
                __CLASS__ . '_PostDispatchSecure',
                $args
            );
        }

        // fire non-secure/legacy-PostDispatch-Events
        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch_' . $this->controller_name,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch_' . $moduleName,
            $args
        );

        Shopware()->Events()->notify(
            __CLASS__ . '_PostDispatch',
            $args
        );
    }

    /**
     * Forward the request to the given controller, module and action with the given parameters.
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array  $params
     */
    public function forward($action, $controller = null, $module = null, array $params = null)
    {
        $request = $this->Request();

        if ($params !== null) {
            $request->setParams($params);
        }
        if ($controller !== null) {
            $request->setControllerName($controller);
            if ($module !== null) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)->setDispatched(false);
    }

    /**
     * Redirect the request. The frontend router will assemble the url.
     *
     * @param string|array $url
     * @param array        $options
     *
     * @throws \Exception
     */
    public function redirect($url, array $options = [])
    {
        if (is_array($url)) {
            $url = $this->Front()->Router()->assemble($url);
        }
        if (!preg_match('#^(https?|ftp)://#', $url)) {
            if (strpos($url, '/') !== 0) {
                $url = $this->Request()->getBaseUrl() . '/' . $url;
            }
            $uri = $this->Request()->getScheme() . '://' . $this->Request()->getHttpHost();
            $url = $uri . $url;
        }

        $this->Response()->setRedirect($url, empty($options['code']) ? 302 : (int) $options['code']);
    }

    /**
     * Set view instance
     *
     * @param Enlight_View $view
     *
     * @return Enlight_Controller_Action
     */
    public function setView(Enlight_View $view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @param Container $loader
     */
    public function setContainer(Container $loader = null)
    {
        $this->container = $loader;
    }

    /**
     * Set front instance
     *
     * @param Enlight_Controller_Front $front
     *
     * @throws \Exception
     *
     * @return Enlight_Controller_Action
     */
    public function setFront(Enlight_Controller_Front $front = null)
    {
        if ($front === null) {
            $front = Shopware()->Container()->get('front');
        }
        $this->front = $front;

        return $this;
    }

    /**
     * Set request instance
     *
     * @param Enlight_Controller_Request_RequestHttp $request
     *
     * @return Enlight_Controller_Action
     */
    public function setRequest(Enlight_Controller_Request_RequestHttp $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set response instance
     *
     * @param Enlight_Controller_Response_ResponseHttp $response
     *
     * @return Enlight_Controller_Action
     */
    public function setResponse(Enlight_Controller_Response_ResponseHttp $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Returns view instance
     *
     * @return Enlight_View_Default|null
     */
    public function View()
    {
        return $this->view;
    }

    /**
     * Returns front controller
     *
     * @throws \Exception
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        if ($this->front === null) {
            $this->setFront();
        }

        return $this->front;
    }

    /**
     * Returns request instance
     *
     * @return Enlight_Controller_Request_RequestHttp
     */
    public function Request()
    {
        return $this->request;
    }

    /**
     * Returns response instance
     *
     * @return Enlight_Controller_Response_ResponseHttp
     */
    public function Response()
    {
        return $this->response;
    }

    /**
     * Get service from resource loader
     *
     * @param string $name
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->container->get($name);
    }

    /**
     * @throws \Exception
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    public function getModelManager()
    {
        return $this->container->get('models');
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string $type    The fully qualified class name of the form type
     * @param mixed  $data    The initial data for the form
     * @param array  $options Options for the form
     *
     * @throws \Exception
     *
     * @return Form
     */
    protected function createForm($type, $data = null, array $options = [])
    {
        return $this->container->get('shopware.form.factory')->create($type, $data, $options);
    }

    protected function getActionArguments(string $actionMethodName): array
    {
        if (!$this->Request()->attributes->has('controllerId')) {
            return [];
        }

        $controllerArray = [
            $this,
            $actionMethodName,
        ];

        $this->Request()->setAttribute('_controller', $this->Request()->getAttribute('controllerId') . ':' . $actionMethodName);

        try {
            return $this->container->get('argument_resolver')->getArguments($this->Request(), $controllerArray);
        } catch (\ReflectionException $e) {
            // Invalid action called
            return [];
        }
    }
}
