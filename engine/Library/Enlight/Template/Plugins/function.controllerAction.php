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
 * Returns the current action's name
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
function smarty_function_controllerAction($params, Enlight_Template_Default $template)
{
    /** @var Enlight_Controller_Front $front */
    $front = Shopware()->Front();
    $request = $front->Request();

    if (empty($request) || !$request instanceof Enlight_Controller_Request_Request) {
        $e = new Exception(
            'Controller view helper requires a valid request object in the front controller instance'
        );
        throw $e;
    }

    return preg_replace('/[^a-zA-Z0-9]/', '', $request->getActionName());
}
