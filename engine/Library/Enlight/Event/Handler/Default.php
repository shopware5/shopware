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
 * Default Enlight event handler.
 *
 * The Enlight_Event_Handler_Default is the basic event handler without any extended functions.
 *
 * @category   Enlight
 * @package    Enlight_Event
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_Handler_Default extends Enlight_Event_Handler
{
    /**
     * @var callable contains the callback function
     */
    protected $listener;

    /**
     * The Enlight_Event_Handler_Default class constructor expects the event name, the callback function and
     * optional the position of the event handler.
     *
     * @param string                                      $event
     * @param callable|array<int, object|string|callable> $listener
     * @param ?int                                        $position
     *
     * @throws Enlight_Exception
     */
    public function __construct($event, $listener, $position = null)
    {
        parent::__construct($event);

        $this->setListener($listener);
        $this->setPosition((int) $position);
    }

    /**
     * Checks if the given listener is callable. If it is callable the listener is set
     * in the internal property and can be accessed by using the getListener() function.
     *
     * @param callable|array<int, object|string|callable> $listener
     *
     * @throws Enlight_Event_Exception
     *
     * @return Enlight_Event_Handler_Default
     */
    public function setListener($listener)
    {
        if (!\is_callable($listener, true, $listener_event)) {
            throw new Enlight_Event_Exception('Listener "' . $listener_event . '" is not callable');
        }
        $this->listener = $listener;

        return $this;
    }

    /**
     * Getter method for the listener property.
     *
     * @return callable
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * Executes the listener with the given Enlight_Event_EventArgs.
     */
    public function execute(Enlight_Event_EventArgs $args)
    {
        return \call_user_func($this->listener, $args);
    }

    /**
     * Returns the handler properties as array.
     *
     * @return array{name: string, position: int, plugin: string, listener: callable}
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'position' => $this->position,
            'plugin' => $this->getName(),
            'listener' => $this->listener,
        ];
    }
}
