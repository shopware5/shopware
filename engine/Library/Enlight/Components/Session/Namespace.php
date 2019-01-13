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

use \Symfony\Component\HttpFoundation\Session\Session as BaseSession;

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Zend_Session_Namespace with an easy array access.
 *
 * @category    Enlight
 *
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Session_Namespace extends BaseSession implements ArrayAccess
{
    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Unset the given offset.
     * @param string $key Key to unset.
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        if ($key == 'sessionId') {
            $this->setId($value);
        } else {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        if ($name == 'sessionId') {
            return $this->getId();
        }
        return parent::get($name, $default);
    }

    /**
     * unsetAll() - unset all variables in this session
     *
     * @return true
     */
    public function unsetAll()
    {
        $this->clear();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }
}