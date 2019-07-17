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

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The Enlight_Event_EventManager stores all event listeners.
 *
 * It allows to execute the events and forward them to
 * the registered listeners. In addition the event manager allows to execute events manuel by using the
 * notify function. <br><br>
 *
 * Example to execute an event manuel:<br>
 *       Shopware()->Events()->notify(
 *           'Enlight_Controller_Front_StartDispatch',
 *           array('subject' => $this)
 *       );
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Event_EventManager extends Enlight_Class
{
    /**
     * @var EventDispatcherInterface 
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher = null)
    {
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
    }

    /**
     * Returns all event listeners of the Enlight_Event_EventManager
     *
     * @return array
     */
    public function getAllListeners()
    {
        return $this->eventDispatcher->getListeners();
    }

    /**
     * $eventManager->addListener('foo.action', array($listener, 'onFooAction'));
     *
     * @param string $eventName
     * @param callback|array<int, object|string> $listener
     * @param int $priority
     *
     * @return Enlight_Event_EventManager
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener(strtolower($eventName), $listener, $priority * -1);

        return $this;
    }

    /**
     * Registers the given event handler and adds it to the internal listeners array.
     * If no event position is set in the event handler, the event handler will be added to the
     * end of the list.
     *
     * @param Enlight_Event_Handler $handler
     *
     * @return Enlight_Event_EventManager
     */
    public function registerListener(Enlight_Event_Handler $handler)
    {
        $eventName = strtolower($handler->getName());
        $this->eventDispatcher->addListener($eventName, $handler->getListener(), $handler->getPosition() * -1);

        return $this;
    }

    /**
     * Removes the listeners for the given event handler.
     *
     * @param Enlight_Event_Handler $handler
     *
     * @return Enlight_Event_EventManager
     */
    public function removeListener(Enlight_Event_Handler $handler)
    {
        $eventName = strtolower($handler->getName());
        $this->eventDispatcher->removeListener($eventName, $handler->getListener());
        
        return $this;
    }

    /**
     * Checks if the given event name has an event listener.
     *
     * @param string $event
     *
     * @return bool
     */
    public function hasListeners($event)
    {
        return $this->eventDispatcher->hasListeners(strtolower($event));
    }

    /**
     * Retrieve a list of listeners registered to a given event.
     *
     * @param $event
     *
     * @return callable[]
     */
    public function getListeners($event)
    {
        return $this->eventDispatcher->getListeners(strtolower($event));
    }

    /**
     * Get a list of events for which this collection has listeners.
     *
     * @return array
     */
    public function getEvents()
    {
        return array_keys($this->eventDispatcher->getListeners());
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
     * @param string $event
     * @param Enlight_Event_EventArgs|array|null $eventArgs
     *
     * @return Enlight_Event_EventArgs|null
     * @throws Enlight_Event_Exception
     *
     */
    public function notify($event, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return null;
        }

        $eventArgs = $this->buildEventArgs($eventArgs);
        $eventArgs->setReturn(null);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);

        foreach ($this->getListeners($event) as $listener) {
            $listener($eventArgs);
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
     * @param string $event
     * @param Enlight_Event_EventArgs|array|null $eventArgs
     *
     * @return Enlight_Event_EventArgs|null
     * @throws Enlight_Exception
     *
     */
    public function notifyUntil($event, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return null;
        }

        $eventArgs = $this->buildEventArgs($eventArgs);
        $eventArgs->setReturn(null);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);

        foreach ($this->getListeners($event) as $listener) {
            if (($return = $listener($eventArgs)) !== null
                || $eventArgs->isProcessed()
            ) {
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
     * @param string $event
     * @param mixed $value
     * @param Enlight_Event_EventArgs|array|null $eventArgs
     *
     * @return mixed
     * @throws Enlight_Event_Exception
     *
     */
    public function filter($event, $value, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return $value;
        }

        $eventArgs = $this->buildEventArgs($eventArgs);
        $eventArgs->setReturn($value);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);

        foreach ($this->getListeners($event) as $listener) {
            if (($return = $listener($eventArgs)) !== null) {
                $eventArgs->setReturn($return);
            }
        }
        $eventArgs->setProcessed(true);

        return $eventArgs->getReturn();
    }

    /**
     * Event which is fired to collect plugin parameters
     * to register additionally application components or configurations.
     *
     * @param string $event
     * @param ArrayCollection $collection
     * @param array|null $eventArgs
     *
     * @return ArrayCollection|null
     * @throws Enlight_Event_Exception
     *
     */
    public function collect($event, ArrayCollection $collection, $eventArgs = null)
    {
        if (!$this->hasListeners($event)) {
            return $collection;
        }

        $eventArgs = $this->buildEventArgs($eventArgs);
        $eventArgs->setName($event);
        $eventArgs->setProcessed(false);

        foreach ($this->getListeners($event) as $listener) {
            $listenerCollection = $listener($eventArgs);
            if ($listenerCollection instanceof ArrayCollection) {
                foreach ($listenerCollection->getValues() as $value) {
                    $collection->add($value);
                }
            } elseif ($listenerCollection !== null) {
                $collection->add($listenerCollection);
            }
        }
        $eventArgs->setProcessed(true);

        return $collection;
    }

    /**
     * @param SubscriberInterface $subscriber
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
            } elseif (is_string($params[0])) {
                $this->addListener($eventName, [$subscriber, $params[0]], isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListener($eventName, [$subscriber, $listener[0]], isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }

    /**
     * Registers all listeners of the given Enlight_Event_Subscriber.
     *
     * @param Enlight_Event_Subscriber $subscriber
     */
    public function registerSubscriber(Enlight_Event_Subscriber $subscriber)
    {
        $listeners = $subscriber->getListeners();

        foreach ($listeners as $listener) {
            $this->registerListener($listener);
        }
    }

    /**
     * Removes all listeners of the given Enlight_Event_Subscriber.
     *
     */
    public function removeSubscriber(Enlight_Event_Subscriber $subscriber)
    {
        $listeners = $subscriber->getListeners();

        foreach ($listeners as $listener) {
            $this->removeListener($listener);
        }
    }

    /**
     * Resets the event listeners.
     *
     * @return Enlight_Event_EventManager
     */
    public function reset()
    {
        foreach ($this->eventDispatcher->getListeners() as $event => $listeners) {
            foreach ($listeners as $listener) {
                $this->eventDispatcher->removeListener($event, $listener);
            }
        }

        return $this;
    }

    /**
     * @param Enlight_Event_EventArgs|array|null $eventArgs
     *
     * @return Enlight_Event_EventArgs
     * @throws Enlight_Event_Exception
     *
     */
    private function buildEventArgs($eventArgs = null)
    {
        if (isset($eventArgs) && is_array($eventArgs)) {
            return new Enlight_Event_EventArgs($eventArgs);
        } elseif (!isset($eventArgs)) {
            return new Enlight_Event_EventArgs();
        } elseif (!$eventArgs instanceof Enlight_Event_EventArgs) {
            throw new Enlight_Event_Exception('Parameter "eventArgs" must be an instance of "Enlight_Event_EventArgs"');
        }

        return $eventArgs;
    }
}
