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

/**
 * Basic class for each controller dispatcher.
 *
 * The Enlight_Controller_Dispatcher is the basic class for a dispatcher. It dispatches with assistance the request
 * to a specified controller action.
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Controller_Dispatcher extends Enlight_Class
{
    /**
     * @var Enlight_Controller_Front Instance of the enlight front controller
     */
    protected $front;

    /**
     * @var Enlight_Controller_Response_Response Instance of the enlight response controller
     */
    protected $response;

    /**
     * Default setter method for the front controller property
     *
     * @param   Enlight_Controller_Front $controller
     * @return  Enlight_Controller_Dispatcher
     */
    public function setFront(Enlight_Controller_Front $controller)
    {
        $this->front = $controller;
        return $this;
    }

    /**
     * Default getter method for the front controller
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        return $this->front;
    }

    /**
     * Default setter method for the response controller
     *
     * @param   Enlight_Controller_Response_Response|null $response
     * @return  Enlight_Controller_Dispatcher
     */
    public function setResponse(Enlight_Controller_Response_Response $response = null)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Default getter method for the response controller
     *
     * @return Enlight_Controller_Response_Response
     */
    public function Response()
    {
        return $this->response;
    }

    /**
     * Default dispatch function of the controller
     *
     * @abstract
     * @param Enlight_Controller_Request_Request   $request
     * @param Enlight_Controller_Response_Response $response
     */
    abstract public function dispatch(Enlight_Controller_Request_Request $request,
                                      Enlight_Controller_Response_Response $response
    );
}
