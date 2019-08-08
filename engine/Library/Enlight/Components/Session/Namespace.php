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

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Zend_Session_Namespace with an easy array access.
 *
 *
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Session_Namespace extends Session implements ArrayAccess
{
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __unset($name)
    {
        return $this->remove($name);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Whether an offset exists
     *
     * @param mixed $key a key to check for
     *
     * @return bool returns true on success or false on failure
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Unset the given offset.
     *
     * @param string $key key to unset
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $key the offset to retrieve
     *
     * @return mixed can return all value types
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Offset to set
     *
     * @param mixed $key   the offset to assign the value to
     * @param mixed $value the value to set
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Count elements of the object
     *
     * @return int The custom count as an integer
     */
    public function count()
    {
        return $this->count();
    }

    public function unsetAll()
    {
        return $this->clear();
    }

    public function clear()
    {
        parent::clear();
        $this->set('sessionId', $this->getId());
    }
}
