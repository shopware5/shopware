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
     * @var array<string, array<Enlight_Event_Handler>> Contains all registered event listeners. A listener can be registered by the registerListener(Enlight_Event_Handler) function.
     */
    protected $listeners = [];

    /**
     * Returns all event listeners of the Enlight_Event_EventManager
     *
     * @return array<string, array<Enlight_Event_Handler>>
     */
    public function getAllListeners()
    {
        return $this->listeners;
    }

    /**
     * $eventManager->addListener('foo.action', array($listener, 'onFooAction'));
     *
     * @param string                             $eventName
     * @param callable|array<int, object|string> $listener
     * @param int                                $priority
     *
     * @return Enlight_Event_EventManager
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        if ($priority === 0) {
            $priority = null;
        }

        $handler = new \Enlight_Event_Handler_Default(
            $eventName,
            $listener,
            $priority
        );

        $this->registerListener($handler);

        return $this;
    }

    /**
     * Registers the given event handler and adds it to the internal listeners array.
     * If no event position is set in the event handler, the event handler will be added to the
     * end of the list.
     *
     * @return Enlight_Event_EventManager
     */
    public function registerListener(Enlight_Event_Handler $handler)
    {
        $eventName = strtolower($handler->getName());

        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $list = &$this->listeners[$eventName];

        if ($handler->getPosition()) {
            $position = (int) $handler->getPosition();
        } else {
            $position = \count($list);
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
     * @return Enlight_Event_EventManager
     */
    public function removeListener(Enlight_Event_Handler $handler)
    {
        $eventName = strtolower($handler->getName());

        if (!isset($this->listeners[$eventName])) {
            return $this;
        }

        $listenerToRemove = $handler->getListener();
        foreach ($this->listeners[$eventName] as $i => $listener) {
            if ($listenerToRemove === $listener->getListener()) {
                unset($this->listeners[$eventName][$i]);
            }
        }

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
        $event = strtolower($event);

        return isset($this->listeners[$event]) && \count($this->listeners[$event]);
    }

    /**
     * Retrieve a list of listeners registered to a given event.
     *
     * @param string $event
     *
     * @return Enlight_Event_Handler[]
     */
    public function getListeners($event)
    {
        $event = strtolower($event);

        return $this->listeners[$event] ?? [];
    }

    /**
     * Get a list of events for which this collection has listeners.
     *
     * @return string[]
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
     * @param string                                                $event
     * @param Enlight_Event_EventArgs|array<string|int, mixed>|null $eventArgs
     *
     * @throws Enlight_Event_Exception
     *
     * @return Enlight_Event_EventArgs|null
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
     * @param string                                                $event
     * @param Enlight_Event_EventArgs|array<string|int, mixed>|null $eventArgs
     *
     * @throws Enlight_Exception
     *
     * @return Enlight_Event_EventArgs|null
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
            if (($return = $listener->execute($eventArgs)) !== null
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
     * @template TValue of mixed
     *
     * @param string                                                $event
     * @param TValue                                                $value
     * @param Enlight_Event_EventArgs|array<string|int, mixed>|null $eventArgs
     *
     * @throws Enlight_Event_Exception
     *
     * @return TValue
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
            if (($return = $listener->execute($eventArgs)) !== null) {
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
     * @param string                      $event
     * @param ArrayCollection<int, mixed> $collection
     * @param array<string, mixed>|null   $eventArgs
     *
     * @throws Enlight_Event_Exception
     *
     * @return ArrayCollection<int, mixed>
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
            $listenerCollection = $listener->execute($eventArgs);
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
     * @return void
     */
    public function addSubscriber(SubscriberInterface $subscriber)
    {
        foreach ($subscriber::getSubscribedEvents() as $eventName => $params) {
            if (\is_string($params)) {
                $this->addListener($eventName, [$subscriber, $params]);
                continue;
            }

            if (\is_string($params[0])) {
                $this->addListener($eventName, [$subscriber, $params[0]], (int) ($params[1] ?? 0));
                continue;
            }

            foreach ($params as $listener) {
                if (\is_array($listener)) {
                    $this->addListener($eventName, [$subscriber, $listener[0]], $listener[1] ?? 0);
                }
            }
        }
    }

    /**
     * Registers all listeners of the given Enlight_Event_Subscriber.
     *
     * @return void
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
     * @return Enlight_Event_EventManager
     */
    public function reset()
    {
        $this->listeners = [];

        return $this;
    }

    /**
     * @param Enlight_Event_EventArgs|array<string|int, mixed>|null $eventArgs
     *
     * @throws Enlight_Event_Exception
     *
     * @return Enlight_Event_EventArgs
     */
    private function buildEventArgs($eventArgs)
    {
        if ($eventArgs instanceof Enlight_Event_EventArgs) {
            return $eventArgs;
        }

        if ($eventArgs === null || \is_array($eventArgs)) {
            return new Enlight_Event_EventArgs((array) $eventArgs);
        }

        throw new Enlight_Event_Exception('Parameter "eventArgs" must be an array or instance of "Enlight_Event_EventArgs"');
    }
}
