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

namespace Shopware\Plugin\Debug\Components;

use Shopware\Components\Logger;

class EventCollector implements CollectorInterface
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var Utils
     */
    private $utils;

    public function __construct(\Enlight_Event_EventManager $eventManager, Utils $utils)
    {
        $this->eventManager = $eventManager;
        $this->utils = $utils;
    }

    public function start()
    {
        $this->eventManager->registerSubscriber($this->getListeners());
    }

    public function logResults(Logger $log)
    {
        foreach (array_keys($this->results) as $event) {
            if (empty($this->results[$event][0])) {
                unset($this->results[$event]);
                continue;
            }
            $listeners = [];
            foreach (Shopware()->Events()->getListeners($event) as $listener) {
                $listener = $listener->getListener();
                if ($listener[0] === $this) {
                    continue;
                }
                if (is_array($listener) && is_object($listener[0])) {
                    $listener[0] = get_class($listener[0]);
                }
                if (is_array($listener)) {
                    $listener = implode('::', $listener);
                }
                $listeners[] = $listener;
            }
            $this->results[$event] = [
                0 => $event,
                1 => $this->utils->formatMemory(0 - $this->results[$event][1]),
                2 => $this->utils->formatTime(0 - $this->results[$event][2]),
                3 => $listeners,
            ];
        }

        $this->results = array_values($this->results);

        foreach ($this->results as $result) {
            $order[] = $result[2];
        }
        array_multisort($order, SORT_NUMERIC, SORT_DESC, $this->results);

        array_unshift($this->results, ['name', 'memory', 'time', 'listeners']);

        $label = 'Benchmark Events';
        $table = [$label,
            $this->results,
        ];

        $log->table($table);
    }

    public function onBenchmarkEvent(\Enlight_Event_EventArgs $args)
    {
        $event = $args->getName();
        if (!isset($this->results[$event])) {
            $this->results[$event] = [
                0 => true,
                1 => 0,
                2 => 0,
            ];
        }

        if (empty($this->results[$event][0])) {
            $this->results[$event][0] = true;
            $this->results[$event][1] -= memory_get_peak_usage(true);
            $this->results[$event][2] -= microtime(true);
        } else {
            $this->results[$event][0] = false;
            $this->results[$event][1] += memory_get_peak_usage(true);
            $this->results[$event][2] += microtime(true);
        }

        return $args->getReturn();
    }

    /**
     * Monitor execution time and memory on specified event points in application
     *
     * @return \Enlight_Event_Subscriber_Array
     */
    public function getListeners()
    {
        $events = $this->eventManager->getEvents();

        $listeners = new \Enlight_Event_Subscriber_Array();

        foreach ($events as $event) {
            if ($event == 'Enlight_Controller_Front_DispatchLoopShutdown') {
                continue;
            }

            $listeners->registerListener(new \Enlight_Event_Handler_Default($event, [$this, 'onBenchmarkEvent'], -1000));
            $listeners->registerListener(new \Enlight_Event_Handler_Default($event, [$this, 'onBenchmarkEvent'], 1000));
        }

        return $listeners;
    }
}
