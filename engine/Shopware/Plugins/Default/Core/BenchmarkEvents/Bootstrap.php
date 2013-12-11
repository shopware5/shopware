<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 */
class Shopware_Plugins_Core_BenchmarkEvents_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    protected $results = array();

    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown'
        );
        return true;
    }

    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        if (!Shopware()->Bootstrap()->hasResource('Log')) {
            return;
        }
        Shopware()->Events()->registerSubscriber($this);
    }

    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        if (!Shopware()->Bootstrap()->hasResource('Log')) {
            return;
        }
        $this->logResults();
    }

    public function logResults()
    {
        foreach (array_keys($this->results) as $event) {
            if (empty($this->results[$event][0])) {
                unset($this->results[$event]);
                continue;
            }
            $listeners = array();
            foreach (Enlight()->Events()->getListeners($event) as $listener) {
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
            $this->results[$event] = array(
                0 => $event,
                1 => $this->formatMemory(0 - $this->results[$event][1]),
                2 => $this->formatTime(0 - $this->results[$event][2]),
                3 => $listeners
            );
        }

        $this->results = array_values($this->results);

        foreach ($this->results as $result) {
            $order[] = $result[2];
        }
        array_multisort($order, SORT_NUMERIC, SORT_DESC, $this->results);

        array_unshift($this->results, array('name', 'memory', 'time', 'listeners'));

        $label = 'Benchmark Events';
        $table = array($label,
            $this->results
        );
        Shopware()->Log()->table($table);
    }

    public function onBenchmarkEvent(Enlight_Event_EventArgs $args)
    {
        $event = $args->getName();
        if (!isset($this->results[$event])) {
            $this->results[$event] = array(
                0 => true,
                1 => 0,
                2 => 0
            );
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

    public function getListeners()
    {
        $events = Shopware()->Events()->getEvents();
        $event_handlers = array();
        foreach ($events as $event) {
            if ($event == 'Enlight_Controller_Front_DispatchLoopShutdown') {
                continue;
            }
            $event_handlers[] = new Enlight_Event_Handler_Default($event, array($this, 'onBenchmarkEvent'), -1000);
            $event_handlers[] = new Enlight_Event_Handler_Default($event, array($this, 'onBenchmarkEvent'), 1000);
        }
        return $event_handlers;
    }

    public function formatMemory($size)
    {
        if (empty($size)) return '0.00 b';
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @number_format($size / pow(1024, ($i = floor(log($size, 1024)))), 2, '.', '') . ' ' . $unit[$i];
    }

    public function formatTime($time)
    {
        return number_format($time, 5, '.', '');
    }
}
