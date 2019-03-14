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

namespace Shopware\Components;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareEventManager extends \Enlight_Event_EventManager
{
    /**
     * Contains all registered event listeners. A listener can be registered by the
     * registerListener(Enlight_Event_Handler $handler) function.
     *
     * @var array<string, array<string, callable>>
     */
    protected $containerListeners = [];

    /**
     * @var ContainerInterface;
     */
    private $container;

    /**
     * @var array
     */
    private $listenerIds = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Adds a service as event listener.
     *
     * @param string $eventName Event for which the listener is added
     * @param array  $callback  The service ID of the listener service & the method
     *                          name that has to be called
     * @param int    $priority  the higher this value, the earlier an event listener
     *                          will be triggered in the chain.
     *                          Defaults to 0
     *
     * @throws \InvalidArgumentException
     */
    public function addListenerService($eventName, $callback, $priority = 0)
    {
        if (!is_array($callback) || count($callback) !== 2) {
            throw new \InvalidArgumentException('Expected an array("service", "method") argument');
        }

        $eventName = strtolower($eventName);
        $this->listenerIds[$eventName][] = [$callback[0], $callback[1], $priority];
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener(\Enlight_Event_Handler $handler)
    {
        $eventName = strtolower($handler->getName());
        $this->lazyLoad($eventName);

        if (isset($this->listenerIds[$eventName])) {
            $listener = $handler->getListener();

            foreach ($this->listenerIds[$eventName] as $i => list($serviceId, $method, $priority)) {
                $key = $serviceId . '.' . $method;

                if (isset($this->containerListeners[$eventName][$key]) && $listener === [$this->containerListeners[$eventName][$key], $method]) {
                    unset($this->containerListeners[$eventName][$key]);
                    if (empty($this->containerListeners[$eventName])) {
                        unset($this->containerListeners[$eventName]);
                    }
                    unset($this->listenerIds[$eventName][$i]);
                    if (empty($this->listenerIds[$eventName])) {
                        unset($this->listenerIds[$eventName]);
                    }
                }
            }
        }

        return parent::removeListener($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName)
    {
        $eventName = strtolower($eventName);
        if (isset($this->listenerIds[$eventName])) {
            return true;
        }

        return parent::hasListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllListeners()
    {
        foreach ($this->listenerIds as $serviceEventName => $args) {
            $this->lazyLoad($serviceEventName);
        }

        return parent::getAllListeners();
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName)
    {
        $eventName = strtolower($eventName);

        $this->lazyLoad($eventName);

        return parent::getListeners($eventName);
    }

    /**
     * Adds a service as event subscriber.
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name (which must implement EventSubscriberInterface)
     */
    public function addSubscriberService($serviceId, $class)
    {
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            $eventName = strtolower($eventName);

            if (is_string($params)) {
                $this->listenerIds[$eventName][] = [$serviceId, $params, 0];
            } elseif (is_string($params[0])) {
                $this->listenerIds[$eventName][] = [$serviceId, $params[0], isset($params[1]) ? $params[1] : 0];
            } else {
                foreach ($params as $listener) {
                    $this->listenerIds[$eventName][] = [$serviceId, $listener[0], isset($listener[1]) ? $listener[1] : 0];
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->containerListeners = [];

        return parent::reset();
    }

    /**
     * Lazily loads listeners for this event from the dependency injection
     * container.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     */
    protected function lazyLoad($eventName)
    {
        if (!isset($this->listenerIds[$eventName])) {
            return;
        }

        foreach ($this->listenerIds[$eventName] as list($serviceId, $method, $priority)) {
            $listener = $this->container->get($serviceId);

            $key = $serviceId . '.' . $method;
            if (!isset($this->containerListeners[$eventName][$key])) {
                $this->addListener($eventName, [$listener, $method], $priority);
            } elseif ($listener !== $this->containerListeners[$eventName][$key]) {
                $handler = new \Enlight_Event_Handler_Default(
                    $eventName,
                    [$this->containerListeners[$eventName][$key], $method]
                );

                parent::removeListener($handler);
                $this->addListener($eventName, [$listener, $method], $priority);
            }

            $this->containerListeners[$eventName][$key] = $listener;
        }
    }
}
