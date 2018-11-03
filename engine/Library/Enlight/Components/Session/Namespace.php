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
 * @category    Enlight
 * @package     Enlight_Session
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 * @version     $Id$
 * @author      Heiner Lohaus
 * @author      $Author$
 */

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Zend_Session_Namespace with an easy array access.
 *
 * @category    Enlight
 * @package     Enlight_Session
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Session_Namespace extends Zend_Session_Namespace implements Countable, IteratorAggregate, ArrayAccess
{
    /**
     * Whether an offset exists
     * @param mixed $key A key to check for.
     * @return boolean Returns true on success or false on failure.
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * Unset the given offset.
     * @param string $key Key to unset.
     */
    public function offsetUnset($key)
    {
        $this->__unset($key);
    }

    /**
     * Offset to retrieve
     * @param mixed $key The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * Offset to set
     * @param mixed $key The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($key, $value)
    {
        $this->__set($key, $value);
    }

    /**
     * Count elements of the object
     * @return int The custom count as an integer
     */
    public function count()
    {
        return $this->apply('count');
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function get($name, $default = null)
    {
        $value = $this->offsetGet($name);
        return $value !== null ? $value : $default;
    }
}
