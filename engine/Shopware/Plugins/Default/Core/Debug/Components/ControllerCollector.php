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

/**
 * @category  Shopware
 * @package   Shopware\Plugin\Debug\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ControllerCollector implements CollectorInterface
{
    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var Utils
     */
    protected $utils;

    /**
     * @var array Contains all measured events.
     */
    protected $results = array();

    /**
     * @var float Contains the start time of the Benchmarking
     */
    protected $startTime;

    /**
     * @var int Contains the start memory size of the Benchmarking
     */
    protected $startMemory;

    /**
     * @param \Enlight_Event_EventManager $eventManager
     * @param Utils $utils
     */
    public function __construct(\Enlight_Event_EventManager $eventManager, Utils $utils)
    {
        $this->eventManager = $eventManager;
        $this->utils = $utils;
    }

    /**
     *
     */
    public function start()
    {
        $this->eventManager->registerSubscriber($this->getListeners());
    }

    /**
     * Get total execution time in controller
     */
    public function logResults(Logger $log)
    {
        $total_time = $this->utils->formatTime(microtime(true) - $this->startTime);
        $label = "Benchmark Controller ($total_time sec)";
        $table = array($label, $this->results);

        $log->table($table);
    }

    /**
     * Monitor execution time and memory on specified event points in application
     *
     * @return \Enlight_Event_Subscriber_Array
     */
    private function getListeners()
    {
        $events = array(
            'Enlight_Controller_Front_RouteStartup',
            'Enlight_Controller_Front_RouteShutdown',
            'Enlight_Controller_Front_DispatchLoopStartup',
            'Enlight_Controller_Front_PreDispatch',
            'Enlight_Controller_Front_PostDispatch',
            'Enlight_Controller_Front_DispatchLoopShutdown',

            'Enlight_Controller_Action_Init',
            'Enlight_Controller_Action_PreDispatch',
            'Enlight_Controller_Action_PostDispatch',

            'Enlight_Plugins_ViewRenderer_PreRender',
            'Enlight_Plugins_ViewRenderer_PostRender'
        );

        $listeners = new \Enlight_Event_Subscriber_Array();
        foreach ($events as $event) {
            $listeners->registerListener(
                new \Enlight_Event_Handler_Default(
                    $event, array($this, 'onBenchmarkEvent'), -99
                )
            );
        }

        return $listeners;
    }

    /**
     * Logs all controller events into the internal log object.
     * Each logged events contains the event name, the execution time and the allocated peak of memory.
     *
     * @param \Enlight_Event_EventArgs $args
     * @return void
     */
    public function onBenchmarkEvent(\Enlight_Event_EventArgs $args)
    {
        if (empty($this->results)) {
            $this->results[] = array('name', 'memory', 'time');
            $this->startTime = microtime(true);
            $this->startMemory = memory_get_peak_usage(true);
        }

        $this->results[] = array(
            0 => str_replace('Enlight_Controller_', '', $args->getName()),
            1 => $this->utils->formatMemory(memory_get_peak_usage(true) - $this->startMemory),
            2 => $this->utils->formatTime(microtime(true) - $this->startTime)
        );
    }
}
