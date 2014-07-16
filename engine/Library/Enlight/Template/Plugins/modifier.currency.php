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
 * @package    Enlight_Template_Plugins
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
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
    if (!Enlight_Application::Instance()->Bootstrap()->hasResource('Currency')) {
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

    $currency = Enlight_Application::Instance()->Currency();
    $value = floatval(str_replace(',', '.', $value));
    $value = $currency->toCurrency($value, $config);
    if (function_exists('mb_convert_encoding')) {
        $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
    }
    $value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);
    return $value;
}
