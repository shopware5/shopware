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
 * Method to fast deploy a query to enlight
 *
 * The params array knows the following keys
 * - name : The name of the action to call
 * - params : optional params array for the specific action call
 *
 * @param                          $params
 * @param Enlight_Template_Default $template
 *
 * @throws Exception
 *
 * @return string
 */
function smarty_function_action($params, Enlight_Template_Default $template)
{
    /** @var Enlight_Controller_Front $front */
    $front = Shopware()->Front();
    $dispatcher = clone $front->Dispatcher();

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
        $userParams = (array) $params['params'];
        unset($params['params']);
    } else {
        $userParams = [];
    }

    $params = array_merge($userParams, $params);

    $request = clone $request;
    $response = clone $response;

    $request->clearParams();
    $response->clearHeaders()
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

    // setParam is used for bc reasons, the attribute should be read for new code
    $request->setParam('_isSubrequest', true);
    $request->setAttribute('_isSubrequest', true);

    $request->setActionName(isset($params['action']) ? $params['action'] : 'index');
    $request->setParams($params)
            ->setDispatched(true);

    Shopware()->Container()->get('request_stack')->push($request);

    $dispatcher->dispatch($request, $response);

    if (!$request->isDispatched() || $response->isRedirect()) {
        // forwards and redirects render nothing
        return '';
    }

    $return = $response->getBody();

    return $return;
}
