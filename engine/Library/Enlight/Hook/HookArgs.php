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
     * @var mixed
     */
    protected $_subject;

    /**
     * @var string
     */
    protected $_method;

    /**
     * @param mixed $subject
     * @param string $method
     * @param array $args
     */
    public function __construct($subject, $method, array $args = array())
    {
        parent::__construct($args);
        $this->_subject = $subject;
        $this->_method = $method;
    }

    /**
     * @return mixed The instance which the hook is executed on.
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * @return string The name of the method whose hooks are being executed.
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @return array The arguments passed to the hooked method.
     */
    public function getArgs()
    {
        return array_values($this->_elements);
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
