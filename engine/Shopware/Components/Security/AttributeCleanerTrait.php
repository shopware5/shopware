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

namespace Shopware\Components\Security;

trait AttributeCleanerTrait
{
    /**
     * Perform cleaning operations on a variable.
     * By default, this method will use PHP strip_tags, however a different callable can be provided as
     * the second parameter of the method, for instance, htmlentities when escaping instead of stripping is needed.
     *
     * NOTE: This method works for strings
     *
     * @param string|object $var      Value to be cleaned
     * @param callable      $callback Function that we will used to perform the cleaning on the attributes
     *
     * @return string|object The filtered string
     */
    protected function cleanup($var, callable $callback = null)
    {
        $callback = $callback ? $callback : 'strip_tags';

        if (!is_string($var) || empty($var)) {
            return $var;
        }

        return $callback($var);
    }
}
