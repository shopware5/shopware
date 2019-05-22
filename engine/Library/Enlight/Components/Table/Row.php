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
 * Enlight table row component is an extension of the zend table row.
 *
 * Reference concrete class that extends Zend_Db_Table_Row_Abstract.
 * Developers may also create their own classes that extend the abstract class.
 * In additional the enlight table row support the hook system.
 *
 * @category   Enlight
 * @package   Enlight_Table
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Table_Row extends Zend_Db_Table_Row implements Enlight_Hook
{
    /**
     * Returns the class name of the given table
     *
     * @param string $tableName
     * @return Zend_Db_Table_Abstract
     */
    protected function _getTableFromString($tableName)
    {
        $tableName = Enlight_Class::getClassName($tableName);
        return parent::_getTableFromString($tableName);
    }
}
