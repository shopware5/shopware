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
 * The Enlight_Event_EventArgs are the arguments of an event handler.
 *
 * It will be created by the event manager and passed to the event listener.
 * The event arguments can be passed to the event manager to execute the event manually.
 *
 * @category   Enlight
 * @package    Enlight_Event
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_EventArgs extends Enlight_Collection_ArrayCollection
{
    /**
     * @var bool Flag whether the listener has finished running.
     */
    protected $_processed;

    /**
     * @var string Contains the name of the event.
     */
    protected $_name;

    /**
     * @var mixed Contains the return value, which can be set by the setReturn method.
     */
    protected $_return;

    /**
     * The Enlight_Event_EventArgs class constructor expects the name of the event.
     *
     * @param              $name
     * @param   array|null $args
     */
    public function __construct(array $args = array())
    {
        parent::__construct($args);
    }

    /**
     * Stops the execution of the listener and sets the processed flag to true.
     *
     * @return Enlight_Event_EventArgs
     */
    public function stop()
    {
        $this->_processed = true;
        return $this;
    }

    /**
     * Setter method for the processed property.
     *
     * @param bool $processed
     *
     * @return Enlight_Event_EventArgs
     */
    public function setProcessed($processed)
    {
        $this->_processed = (bool) $processed;
        return $this;
    }

    /**
     * Getter method for the processed property.
     *
     * @return bool
     */
    public function isProcessed()
    {
        return $this->_processed;
    }

    /**
     * Setter method for the event name property.
     *
     * @param   $name
     * @return  string
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Getter method for the event name property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Setter method for the return property.
     *
     * @param   mixed $return
     * @return  void
     */
    public function setReturn($return)
    {
        $this->_return = $return;
    }

    /**
     * Getter method for the return property.
     *
     * @return  mixed
     */
    public function getReturn()
    {
        return $this->_return;
    }
}
