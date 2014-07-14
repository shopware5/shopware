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
 * Return the device type
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
function smarty_function_device($params, Enlight_Template_Default $template)
{
    /** @var $front Enlight_Controller_Front */
    $front = Enlight_Application::Instance()->Front();

    $request = $front->Request();

    if (empty($request)) {
        throw new \RuntimeException('Device view helper requires a registered request object in the front controller instance');
    }

    return $request->getDeviceType();
}
