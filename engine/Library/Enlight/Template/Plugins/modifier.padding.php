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
 * Fills a string to a given width by appending $fill. If the given string is longer than
 * the given width the string will be shortened and $break will be appended.
 *
 * @param string $str
 * @param int    $width
 * @param string $break
 * @param string $fill
 * @return string
 */
function smarty_modifier_padding($str, $width = 10, $break = '...', $fill = ' ')
{
    // checks if we have either a integer, float, sting or boolean value
    // If we don't get what we expected, we use some default values
    if (!is_scalar($break)) {
        $break = '...';
    }
    if (empty($fill) || !is_scalar($fill)) {
        $fill = ' ';
    }
    if (empty($width) || !is_numeric($width)) {
        $width = 10;
    } else {
        $width = (int) $width;
    }
    // if no string is given, just build one string containing the fill pattern
    if (!is_scalar($str)) {
        return str_repeat($fill, $width);
    }
    // If the string longer than the given width shorten the string and append the break pattern
    if (strlen($str) > $width) {
        $str = substr($str, 0, $width - strlen($break)) . $break;
    }
    // If the string is shorter than the given width - fill the remaining space with the filling pattern
    if ($width > strlen($str)) {
        return str_repeat($fill, $width - strlen($str)) . $str;
    } else {
        return $str;
    }
}
