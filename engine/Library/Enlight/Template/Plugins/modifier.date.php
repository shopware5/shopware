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
 * Format an given Date to local specific rules.
 *
 * @see http://framework.zend.com/manual/de/zend.date.constants.html
 *
 * @param string $value
 * @param string $format
 * @param string $type
 *
 * @return int|mixed|null|string
 */
function smarty_modifier_date($value, $format = null, $type = null)
{
    if ($value === 'r') {
        $value = $format;
        $format = 'r';
        $type = 'php';
    }
    if (empty($value)) {
        return '';
    }
    if (!empty($format) && is_string($format)) {
        if (defined('Zend_Date::' . strtoupper($format))) {
            $format = constant('Zend_Date::' . strtoupper($format));
        }
    }
    if (!empty($type) && is_string($type)) {
        $type = strtolower($type);
    }

    /** @var Zend_Locale $locale */
    $locale = Shopware()->Container()->get('locale');
    if (is_string($value)) {
        $value = strtotime($value);
    } elseif ($value instanceof DateTime) {
        /** @var \DateTime $value */
        $value = $value->getTimestamp();
    }

    $date = new Zend_Date($value, Zend_Date::TIMESTAMP, $locale);

    $value = $date->toString($format, $type);

    $value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);

    return $value;
}
