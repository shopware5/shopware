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
 * Returns a tax value in local specific format.
 *
 * @param int|float $value
 * @param string     $locale
 * @return mixed
 */
function smarty_modifier_tax($value, $locale = null)
{
    if (!is_numeric($value)) {
        throw new InvalidArgumentException('Input ' . (string) $value . ' must be numeric.');
    }

    $format['precision'] = 2;
    if (!$locale) {
        $format['locale'] = Shopware()->Container()->get('locale');
    } else {
        $format['locale'] = $locale;
    }

    // check if value is integer
    if ((int)($value) == $value) {
        $format['precision'] = 0;
    }

    return Zend_Locale_Format::toNumber($value, $format);
}
