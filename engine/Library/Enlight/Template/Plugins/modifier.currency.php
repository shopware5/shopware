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
 * Formats a given decimal value to a local aware currency value
 *
 *
 * @link http://framework.zend.com/manual/de/zend.currency.options.html
 * @param float  $value Value can have a coma as a decimal separator
 * @param array  $config
 * @param string $position where the currency symbol should be displayed
 * @return float|string
 */
function smarty_modifier_currency($value, $config = null, $position = null)
{
    if (!Shopware()->Container()->has('Currency')) {
        return $value;
    }

    if (!empty($config) && is_string($config)) {
        $config = strtoupper($config);
        if (defined('Zend_Currency::' . $config)) {
            $config = array('display' => constant('Zend_Currency::' . $config));
        } else {
            $config = array();
        }
    } else {
        $config = array();
    }

    if (!empty($position) && is_string($position)) {
        $position = strtoupper($position);
        if (defined('Zend_Currency::' . $position)) {
            $config['position'] = constant('Zend_Currency::' . $position);
        }
    }

    $currency = Shopware()->Container()->get('Currency');
    $value = floatval(str_replace(',', '.', $value));
    $value = $currency->toCurrency($value, $config);
    $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
    $value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);
    return $value;
}
