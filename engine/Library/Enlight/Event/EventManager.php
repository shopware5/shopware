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
 * @package    Enlight_Event
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * The Enlight_Event_EventManager stores all event listeners.
 *
 * It allows to execute the events and forward them to
 * the registered listeners. In addition the event manager allows to execute events manuel by using the
 * notify function. <br><br>
 *
 * Example to execute an event manuel:<br>
 *       Enlight_Application::Instance()->Events()->notify(
 *           'Enlight_Controller_Front_StartDispatch',
 *           array('subject' => $this)
 *       );
 *
 * @category   Enlight
 * @package    Enlight_Event
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_EventManager extends Enlight_Class
{
    /**
     * @var array Contains all registered event listeners. A listener can be registered by the
     * registerListener(Enlight_Event_Handler $handler) function.
     */
    protected $listeners = array();

    /**
     * Returns all event listeners of the Enlight_Event_EventManager
     * @return array
     */
    public function getAllListeners()
    {
        return $this->listeners;
    }

    /**
     * Registers the given event handler and adds it to the internal listeners array.
     * If no event position is set in the event handler, the event handler will be added to the
     * end of the list.
     *
     * @param   Enlight_Event_Handler $handler
     * @return  Enlight_Event_EventManager
     */
    public function registerListener(Enlight_Event_Handler $handler)
    {
        $list =& $this->listeners[$handler->getName()];

        if ($handler->getPosition()) {
            $position = (int) $handler->getPosition();
        } else {
            $position = count($list);
        }
        while (isset($list[$position])) {
            ++$position;
        }
        $list[$position] = $handler;

        ksort($list);

        return $this;
    }

    /**
     * Removes the listeners for the given event handler.
     *
     * @param   Enlight_Event_Handler $handler
     * @return  Enlight_Event_EventManager
     */
    public function removeListener(Enlight_Event_Handler $handler)
    {
        if (!empty($this->listeners[$handler->getName()])) {
            $this->listeners[$handler->getName()] = array_diff($this->listeners[$handler->getName()], array($handler));
        }
        return $this;
    }

    /**
     * Checks if the given event name has an event listener.
     * @param   string $event
     * @return  bool
     */
    public function hasListeners($event)
    {
        return isset($this->listeners[$event]) && count($this->listeners[$event]);
    }

    /**
     * Retrieve a list of listeners registered to a given event.
     *
     * @param   $event
     * @return  Enlight_Event_Handler[]
     */
    public function getListeners($event)
    {
        if (isset($this->listeners[$event])) {
            return $this->listeners[$event];
        } else {
            return array();
        }
    }

    /**
     * Get a list of events for which this collection has listeners.
     *
     * @return  array
     */
    public function getEvents()
    {
        return array_keys($this->listeners);
    }

    /**
     * Checks if the event has registered listeners.
     * If the event has listeners this listeners will be executed with the given event arguments.
     * The event arguments have to been an array or an instance of the Enlight_Event_EventArgs class.
     * If the given arguments not an array or an instance of the Enlight_Event_EventArgs class enlight
     * throw an Enlight_Event_Exception.
     * Before the listener will be executed the the flag "processed" will be set to false in the event arguments.
     * After all event listeners has been executed the "processed" flag will be set to true.
     *
     * @throws  Enlight_Event_Exception
     * @param   string $event
     * @param   Enlight_Event_EventArgs|array|null $eventArgs
     * @return  Enlight_Event_EventArgs|null
     */
    public function notify($event, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return null;
        }
        if (isset($eventArgs) && is_array($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs($eventArgs);
        } elseif (!isset($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs();
        } elseif (!$eventArgs instanceof Enlight_Event_EventArgs) {
            throw new Enlight_Event_Exception('Parameter "eventArgs" must be an instance of "Enlight_Event_EventArgs"');
        }
        $eventArgs->setReturn(null);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);
        foreach ($this->getListeners($event) as $listener) {
            $listener->execute($eventArgs);
        }
        $eventArgs->setProcessed(true);
        return $eventArgs;
    }

    /**
     * Checks if the event has registered listeners.
     * If the event has listeners this listeners will be executed with the given event arguments.
     * The event arguments have to been an array or an instance of the Enlight_Event_EventArgs class.
     * If the given arguments not an array or an instance of the Enlight_Event_EventArgs class enlight
     * throw an Enlight_Event_Exception.
     * Before the listener will be executed the the flag "processed" will be set to false in the event arguments.
     * After all event listeners has been executed the "processed" flag will be set to true.
     *
     * The event listeners will be executed until one of the listeners return not null.
     *
     * @throws  Enlight_Exception
     * @param   string $event
     * @param   Enlight_Event_EventArgs|array|null $eventArgs
     * @return  Enlight_Event_EventArgs|null
     */
    public function notifyUntil($event, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return null;
        }
        if (isset($eventArgs) && is_array($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs($eventArgs);
        } elseif (!isset($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs();
        } elseif (!$eventArgs instanceof Enlight_Event_EventArgs) {
            throw new Enlight_Exception('Parameter "eventArgs" must be an instance of "Enlight_Event_EventArgs"');
        }
        $eventArgs->setReturn(null);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);
        foreach ($this->getListeners($event) as $listener) {
            if (null !== ($return = $listener->execute($eventArgs))
              || $eventArgs->isProcessed()) {
                $eventArgs->setProcessed(true);
                $eventArgs->setReturn($return);
            }
            if ($eventArgs->isProcessed()) {
                return $eventArgs;
            }
        }
        return null;
    }

    /**
     * Checks if the event has registered listeners.
     * If the event has listeners this listeners will be executed with the given event arguments.
     * The event arguments have to been an array or an instance of the Enlight_Event_EventArgs class.
     * If the given arguments not an array or an instance of the Enlight_Event_EventArgs class enlight
     * throw an Enlight_Event_Exception.
     * Before the listener will be executed the the flag "processed" will be set to false in the event arguments.
     * After all event listeners has been executed the "processed" flag will be set to true.
     *
     * The return value of the execute method will be set in the event arguments return value.
     *
     * @throws  Enlight_Event_Exception
     * @param   string $event
     * @param   mixed $value
     * @param   Enlight_Event_EventArgs|array|null $eventArgs
     * @return  mixed
     */
    public function filter($event, $value, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return $value;
        }
        if (isset($eventArgs) && is_array($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs($eventArgs);
        } elseif (!isset($eventArgs)) {
            $eventArgs = new Enlight_Event_EventArgs();
        } elseif (!$eventArgs instanceof Enlight_Event_EventArgs) {
            throw new Enlight_Event_Exception('Parameter "eventArgs" must be an instance of "Enlight_Event_EventArgs"');
        }
        $eventArgs->setReturn($value);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);
        foreach ($this->getListeners($event) as $listener) {
            if (null !== ($return = $listener->execute($eventArgs))) {
                $eventArgs->setReturn($return);
            }
        }
        $eventArgs->setProcessed(true);
        return $eventArgs->getReturn();
    }

    /**
     * Registers all listeners of the given Enlight_Event_Subscriber.
     *
     * @param   Enlight_Event_Subscriber $subscriber
     * @return  void
     */
    public function registerSubscriber(Enlight_Event_Subscriber $subscriber)
    {
        $listeners = $subscriber->getListeners();
        foreach ($listeners as $listener) {
            $this->registerListener($listener);
        }
    }

    /**
     * Resets the event listeners.
     *
     * @return  Enlight_Event_EventManager
     */
    public function reset()
    {
        $this->listeners = array();
        return $this;
    }

    /**
     * @param \Enlight_Application $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }
}
