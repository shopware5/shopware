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
 * Table component, which performs all actions for the specified table.
 *
 * The Enlight_Components_Table extend the zend db table with the hook ability.
 *
 *
 * @category   Enlight
 * @package    Enlight_Table
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Table extends Zend_Db_Table implements Enlight_Hook
{
    /**
     * Returns a normalized version of the reference map
     *
     * @return array
     */
    protected function _getReferenceMapNormalized()
    {
        $maps = parent::_getReferenceMapNormalized();
        foreach ($maps as $rule => $map) {
            if (isset($map[self::REF_TABLE_CLASS])) {
                $maps[$rule][self::REF_TABLE_CLASS] = Enlight_Class::getClassName($map[self::REF_TABLE_CLASS]);
            }
        }
        return $maps;
    }
}
