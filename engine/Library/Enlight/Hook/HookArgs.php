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
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Hook arguments which will be passed to the hook listener.
 *
 * The Enlight_Hook_HookArgs is an array of hook arguments which are passed by the manager to the hook handler.
 * It contains all data about the hook handler (class name, method name, target function, return value)
 *
 * @category   Enlight
 * @package    Enlight_Hook
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Hook_HookArgs extends Enlight_Event_EventArgs
{
    /**
     * Default getter function to return the class property
     *
     * @return mixed
     */
    public function getSubject()
    {
        return $this->get('class');
    }

    /**
     * Default getter function to return the method property
     * @return mixed
     */
    public function getMethod()
    {
        return $this->get('method');
    }

    /**
     * Default getter function to return the array values of the elements property
     * @return array
     */
    public function getArgs()
    {
        return array_slice(array_values($this->_elements), 2);
    }

    /**
     * Sets the given property to null.
     *
     * @param   $key
     * @return  \Enlight_Hook_HookArgs
     */
    public function remove($key)
    {
        $this->set($key, null);
        return $this;
    }

    /**
     * Default set function to set the value to the given property
     *
     * @param   $key
     * @param   $value
     * @return  \Enlight_Hook_HookArgs
     */
    public function set($key, $value)
    {
        if ($this->containsKey($key)) {
            parent::set($key, $value);
        }
        return $this;
    }
}
