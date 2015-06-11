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
 * Returns a number in local specific format.
 *
 * @link http://framework.zend.com/manual/de/zend.locale.parsing.html
 * @param int|float $value
 * @param array $options
 * @return mixed
 */
function smarty_modifier_number($value, $options = array())
{
    $options['locale'] = empty($options['locale']) ? Enlight_Application::Instance()->Locale() : $options['locale'];
    $options['precision'] = empty($options['precision']) ? 2 : $options['precision'];

    //remove all separators
    $value = str_replace(['.', ','], '', $value, $availableSeparators);

    // add decimal separator
    if ($availableSeparators > 0) {
        $value = substr_replace($value, '.', strlen($value) - $options['precision'], 0);
    }
    return Zend_Locale_Format::toNumber($value, $options);
}
