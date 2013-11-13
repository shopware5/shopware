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
 * @package    Enlight_Template_Plugins
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Method to fast deploy a query to enlight
 *
 * The params array knows the following keys
 * - name : The name of the action to call
 * - params : optional params array for the specific action call
 *
 * @param                          $params
 * @param Enlight_Template_Default $template
 * @return string
 * @throws Exception
 */
function smarty_function_action($params, Enlight_Template_Default $template)
{
    /** @var $front Enlight_Controller_Front */
    $front = Enlight_Application::Instance()->Front();
    $dispatcher = clone $front->Dispatcher();

    $modules = $dispatcher->getControllerDirectory();
    if (empty($modules)) {
        $e = new Exception('Action helper depends on valid front controller instance');
        //$e->setView($view);
        throw $e;
    }

    $request = $front->Request();
    $response = $front->Response();

    if (empty($request) || empty($response)) {
        $e = new Exception(
            'Action view helper requires both a registered request and response object in the front controller instance'
        );
        //$e->setView($view);
        throw $e;
    }

    if (isset($params['name'])) {
        $params['action'] = $params['name'];
        unset($params['name']);
    }
    if (isset($params['params'])) {
        $userParams = (array)$params['params'];
        unset($params['params']);
    } else {
        $userParams = array();
    }

    $params = array_merge($userParams, $params);

    $request  = clone $request;
    $response = clone $response;

    $request->clearParams();
    $response->clearHeaders()
             ->clearRawHeaders()
             ->clearBody();

    if (isset($params['module'])) {
        $request->setModuleName($params['module'])
                ->setControllerName('index')
                ->setActionName('index');
    }
    if (isset($params['controller'])) {
        $request->setControllerName($params['controller'])
                ->setActionName('index');
    }
    $request->setParam('_isSubrequest', true);

    $request->setActionName(isset($params['action']) ? $params['action'] : 'index');
    $request->setParams($params)
            ->setDispatched(true);

    $dispatcher->dispatch($request, $response);

    if (!$request->isDispatched() || $response->isRedirect()) {
        // forwards and redirects render nothing
        return '';
    }

    $return = $response->getBody();

    return $return;
}
