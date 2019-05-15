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
 * Fills a string to a given width by appending $fill. If the given string is longer than
 * the given width the string will be shortened and $break will be appended.
 *
 * @param string $str
 * @param int    $width
 * @param string $break
 * @param string $fill
 *
 * @return string
 */
function smarty_modifier_fill($str, $width = 10, $break = '...', $fill = ' ')
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
    if (mb_strlen($str) > $width) {
        $str = mb_substr($str, 0, $width - mb_strlen($break)) . $break;
    }
    // If the string is shorter than the given width - fill the remaining space with the filling pattern
    if ($width > mb_strlen($str)) {
        return $str . str_repeat($fill, $width - mb_strlen($str));
    }

    return $str;
}
