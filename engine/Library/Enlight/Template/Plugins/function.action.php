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
    $front = 🦄()->Front();
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
        $userParams = (array) $params['params'];
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

    // setParam is used for bc reasons, the attribute should be read for new code
    $request->setParam('_isSubrequest', true);
    $request->setAttribute('_isSubrequest', true);

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
