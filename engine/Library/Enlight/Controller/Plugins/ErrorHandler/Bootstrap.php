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

/**
 * Controller of the enlight error handler plugin
 *
 * The Enlight_Controller_Plugins_ErrorHandler_Bootstrap is a default controller plugin to catch possibly occurring
 * exceptions in the controller. It will catch the exceptions, pass them to an error controller and display them.
 *
 * @category   Enlight
 * @package    Enlight_Controller
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Controller_Plugins_ErrorHandler_Bootstrap extends Enlight_Plugin_Bootstrap_Default
{
    /**
     * Initials the default event handlers onRouteShutdown and onPostDispatch
     * @return void
     */
    public function init()
    {
        $event = new Enlight_Event_Handler_Default(
            'Enlight_Controller_Front_RouteShutdown',
            array($this, 'onRouteShutdown'),
            500
        );
        $this->Application()->Events()->registerListener($event);

        $event = new Enlight_Event_Handler_Default(
            'Enlight_Controller_Front_PostDispatch',
            array($this, 'onPostDispatch'),
            500
        );
        $this->Application()->Events()->registerListener($event);
    }

    /**
     * Listener method for the Enlight_Controller_Front_RouteShutdown event.
     *
     * @param   Enlight_Event_EventArgs $args
     * @return  void
     */
    public function onRouteShutdown(Enlight_Event_EventArgs $args)
    {
        $this->handleError($args->getSubject(), $args->getRequest());
    }

    /**
     * Listener method for the Enlight_Controller_Front_PostDispatch event.
     *
     * @param   Enlight_Event_EventArgs $args
     * @return  void
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $this->handleError($args->getSubject(), $args->getRequest());
    }

    /**
     * Flag; are we already inside the error handler loop?
     *
     * @var bool
     */
    protected $_isInsideErrorHandlerLoop = false;

    /**
     * Exception count logged at first invocation of plugin
     *
     * @var int
     */
    protected $_exceptionCountAtFirstEncounter = 0;


    /**
     * The handle error function checks for an exception and
     * allows the error handler controller the option to forward
     *
     * @param Enlight_Controller_Front $front
     * @param Enlight_Controller_Request_Request $request
     * @return mixed
     * @throws mixed
     */
    protected function handleError($front, Enlight_Controller_Request_Request $request)
    {
        if ($front->getParam('noErrorHandler')) {
            return;
        }

        $response = $front->Response();

        if ($this->_isInsideErrorHandlerLoop) {
            $exceptions = $response->getException();
            if (count($exceptions) > $this->_exceptionCountAtFirstEncounter) {
                // Exception thrown by error handler; tell the front controller to throw it
                $front->throwExceptions(true);
                throw array_pop($exceptions);
            }
        }

        // check for an exception AND allow the error handler controller the option to forward
        if (($response->isException()) && (!$this->_isInsideErrorHandlerLoop)) {
            $this->_isInsideErrorHandlerLoop = true;

            // Get exception information
            $error            = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
            $exceptions       = $response->getException();
            $exception        = $exceptions[0];
            $error->exception = $exception;

            // Keep a copy of the original request
            $error->request = clone $request;

            // get a count of the number of exceptions encountered
            $this->_exceptionCountAtFirstEncounter = count($exceptions);

            // Forward to the error handler
            $request->setParam('error_handler', $error)
                    ->setControllerName('error')
                    ->setActionName('error')
                    ->setDispatched(false);
        }
    }
}
