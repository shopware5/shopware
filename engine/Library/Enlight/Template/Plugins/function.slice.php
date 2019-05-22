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
 * Slices an array into chunks
 *
 * params knows the following keys
 * - array  : Array to slice
 * - offset : Where to start to slice
 * - length : How long a single slice should be
 * - assign : Smarty variable to assign the sliced array
 *
 * @param array  $params
 * @param object $smarty
 */
function smarty_function_slice($params, $smarty)
{
    $array = array_slice($params['array'], $params['offset'], $params['length']);
    $smarty->assign($params['assign'], $array);
}
