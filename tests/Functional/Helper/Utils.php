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

namespace Shopware\Tests\Functional\Helper;

class Utils
{
    public static function bindAndCall(callable $fn, $newThis, $args = [], $bindClass = null)
    {
        $func = \Closure::bind($fn, $newThis, $bindClass ?: get_class($newThis));
        if ($args) {
            return call_user_func_array($func, $args);
        }

        return $func(); //faster
    }

    /**
     * Changes a property value of an object. (hijack because you can also change private/protected properties)
     *
     * @param object $object
     * @param string $propertyName
     */
    public static function hijackProperty($object, $propertyName, $newValue)
    {
        self::bindAndCall(function () use ($object, $propertyName, $newValue) {
            $object->$propertyName = $newValue;
        }, $object);
    }

    public static function hijackMethod($object, $methodName, array $arguments = [])
    {
        return self::bindAndCall(function () use ($object, $methodName, $arguments) {
            return call_user_func_array([$object, $methodName], $arguments);
        }, $object);
    }

    public static function hijackAndReadProperty($object, $propertyName)
    {
        $ret = self::bindAndCall(function () use ($object, $propertyName) {
            return $object->$propertyName;
        }, $object);

        return $ret;
    }
}
