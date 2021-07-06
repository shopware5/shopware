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
 * Basic class for each Enlight event handler.
 *
 * The Enlight_Event_Handler is the basic class for each specified event handler.
 * The Enlight_Event_EventManager executes the registered event handlers.
 *
 * @category   Enlight
 * @package    Enlight_Event
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Event_Handler
{
    /**
     * @var string Contains the name of the event handler
     */
    protected $name;

    /**
     * @var int contains the event position of the event handler
     */
    protected $position;

    /**
     * The Enlight_Event_Handler class constructor expects an event name. If no name is given,
     * the constructor throws an Enlight_Event_Exception.
     *
     * @throws Enlight_Event_Exception
     */
    public function __construct($event)
    {
        if ($event === null) {
            throw new Enlight_Event_Exception('Parameter event cannot be empty.');
        }
        $this->name = $event;
    }

    /**
     * Getter method for the name property. Contains the event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Getter method for the position property. Contains the event position of the event handler.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Setter method for the position property.
     *
     * @param int $position
     *
     * @return Enlight_Event_Handler
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * Getter method for the listener property.
     *
     * @return callable
     */
    abstract public function getListener();

    /**
     * Executes the event handler with the Enlight_Event_EventArgs.
     */
    abstract public function execute(Enlight_Event_EventArgs $args);
}
