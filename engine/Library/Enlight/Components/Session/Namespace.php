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

use \Symfony\Component\HttpFoundation\Session\Session as BaseSession;
use \Shopware\Components\Session\SessionInterface;

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Zend_Session_Namespace with an easy array access.
 *
 * @category    Enlight
 * @package     Enlight_Session
 * @copyright   Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license     http://enlight.de/license     New BSD License
 * @deprecated  Use \Shopware\Components\Session\SessionInterface instead
 */
class Enlight_Components_Session_Namespace extends BaseSession implements SessionInterface, ArrayAccess
{
    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * {@inheritdoc}
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