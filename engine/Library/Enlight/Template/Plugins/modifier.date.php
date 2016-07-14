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
 * Format an given Date to local specific rules.
 *
 * @link http://framework.zend.com/manual/de/zend.date.constants.html
 * @param string $value
 * @param string $format
 * @param string $type
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
        /** @var $value DateTime */
        $value = $value->getTimestamp();
    }

    $date = new Zend_Date($value, Zend_Date::TIMESTAMP, $locale);

    $value = $date->toString($format, $type);

    $value = htmlentities($value, ENT_COMPAT, 'UTF-8', false);

    return $value;
}
