<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventManager;
use Enlight_Event_Handler;
use Enlight_Event_Handler_Default;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareEventManager extends Enlight_Event_EventManager
{
    private const DEPRECATED_EVENTS = [
        'Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd',
        'Shopware_Modules_Articles_sGetArticlesByCategory_FilterResult',
        'Shopware_Modules_Articles_GetArticleById_FilterResult',
    ];

    /**
     * Contains all registered event listeners. A listener can be registered by the
     * registerListener(Enlight_Event_Handler $handler) function.
     *
     * @var array<string, array<string, callable|object>>
     */
    protected $containerListeners = [];

    private ContainerInterface $container;

    private array $listenerIds = [];

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
     * @throws InvalidArgumentException
     */
    public function addListenerService($eventName, $callback, $priority = 0)
    {
        if (!\is_array($callback) || \count($callback) !== 2) {
            throw new InvalidArgumentException('Expected an array("service", "method") argument');
        }

        $eventName = strtolower($eventName);
        $this->listenerIds[$eventName][] = [$callback[0], $callback[1], $priority];
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener(Enlight_Event_Handler $handler)
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
     * @param string                            $serviceId The service ID of the subscriber service
     * @param class-string<SubscriberInterface> $class     The service's class name (which must implement EventSubscriberInterface)
     */
    public function addSubscriberService($serviceId, $class)
    {
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            $eventName = strtolower($eventName);

            if (\is_string($params)) {
                $this->listenerIds[$eventName][] = [$serviceId, $params, 0];
                continue;
            }

            if (\is_string($params[0])) {
                $this->listenerIds[$eventName][] = [$serviceId, $params[0], (int) ($params[1] ?? 0)];
                continue;
            }

            foreach ($params as $listener) {
                if (\is_array($listener)) {
                    $this->listenerIds[$eventName][] = [$serviceId, $listener[0], $listener[1] ?? 0];
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

    public function addListener($eventName, $listener, $priority = 0)
    {
        $this->checkForDeprecatedEvent($eventName);

        return parent::addListener($eventName, $listener, $priority);
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
            if ($listener === null) {
                continue;
            }

            $key = $serviceId . '.' . $method;
            if (!isset($this->containerListeners[$eventName][$key])) {
                $this->addListener($eventName, [$listener, $method], $priority);
            } elseif ($listener !== $this->containerListeners[$eventName][$key]) {
                $handler = new Enlight_Event_Handler_Default(
                    $eventName,
                    [$this->containerListeners[$eventName][$key], $method]
                );

                parent::removeListener($handler);
                $this->addListener($eventName, [$listener, $method], $priority);
            }

            $this->containerListeners[$eventName][$key] = $listener;
        }
    }

    private function checkForDeprecatedEvent(string $eventName): void
    {
        if (\in_array($eventName, self::DEPRECATED_EVENTS, true)) {
            $this->container->get('corelogger')->warning(sprintf('Event "%s" is deprecated. Do not use it anymore.', $eventName));
        }
    }
}
