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
 * Returns known translations.
 *
 * @link http://framework.zend.com/manual/de/zend.locale.functions.html
 * @param string $value
 * @param string $path
 * @param string $locale
 * @return string|null
 */
function smarty_modifier_translate($value = null, $path = null, $locale = null)
{
    if (!Shopware()->Container()->has('locale')) {
        return $value;
    }
    if ($locale === null) {
        $locale = Shopware()->Container()->get('locale');
    }
    if ($path === 'currency') {
        $path = 'nametocurrency';
    }
    return $locale->getTranslation($value, $path, $locale);
}
