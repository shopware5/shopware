<?php

declare(strict_types=1);
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

use Closure;
use RuntimeException;

class Utils
{
    /**
     * @return array<mixed>|callable|null
     */
    public static function bindAndCall(Closure $fn, object $newThis)
    {
        $func = Closure::bind($fn, $newThis, \get_class($newThis));
        if (!$func instanceof Closure) {
            throw new RuntimeException('Could not create closure function');
        }

        return $func(); //faster
    }

    /**
     * Changes a property value of an object. (hijack because you can also change private/protected properties)
     *
     * @param mixed|null $newValue
     */
    public static function hijackProperty(object $object, string $propertyName, $newValue): void
    {
        self::bindAndCall(function () use ($object, $propertyName, $newValue) {
            $object->$propertyName = $newValue;
        }, $object);
    }

    /**
     * @return array<mixed>|callable|null
     */
    public static function hijackAndReadProperty(object $object, string $propertyName)
    {
        return self::bindAndCall(function () use ($object, $propertyName) {
            return $object->$propertyName;
        }, $object);
    }
}
