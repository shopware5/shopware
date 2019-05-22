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
 * @param array        $value
 * @param string|array $keys
 *
 * @return array
 */
function smarty_modifier_array_unset($value, $keys)
{
    if (!is_array($value)) {
        return $value;
    }

    if (is_string($keys)) {
        $keys = [$keys];
    }

    $array = $value;

    foreach ($keys as $key) {
        unset($array[$key]);
    }

    return $array;
}
