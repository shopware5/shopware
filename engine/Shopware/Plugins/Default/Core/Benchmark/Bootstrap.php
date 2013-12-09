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
 * Enlight benchmark extension to benchmark database queries, controller actions and template rendering.
 *
 * The Enlight_Extensions_Benchmark_Bootstrap allows the timekeeping, memory measurement.
 * It writes the benchmark data into the log. Support the benchmarking of database request,
 * template rendering and controller events.
 */
class Shopware_Plugins_Core_Benchmark_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Components_Log Contains an instance of the Enlight_Components_Log
     */
    protected $log;

    /**
     * @var Doctrine\DBAL\Logging\DebugStack(
     */
    protected $modelLogger;

    /**
     * @var array Contains all measured events.
     */
    protected $results = array();

    /**
     * @var null Contains the start time of the Benchmarking
     */
    protected $startTime;

    /**
     * @var null Contains the start memory size of the Benchmarking
     */
    protected $startMemory;

    /**
     * Install benchmark plugin.
     * Subscribes the Enlight_Controller_Front_StartDispatch event to start the benchmarking and
     * the Enlight_Controller_Front_DispatchLoopShutdown to stop the benchmarking.
     *
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_StartDispatch',
            'onStartDispatch',
            100
        );

        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            'onDispatchLoopShutdown',
            100
        );
        return true;
    }


    /**
     * Returns the instance of the enlight configuration. If no configuration is set,
     * the logDb, logTemplate and the logController flags will be set to true.
     *
     * @return  Enlight_Config
     */
    public function Config()
    {
        $config = parent::Config();
        if(count($config) === 0) {
            $config->merge(new Enlight_Config(array(
                'logDb' => true,
                'logTemplate' => true,
                'logController' => true,
                'logModel' => true,
            )));
        }
        return $config;
    }

    /**
     * Listener method of the Enlight_Controller_Front_StartDispatch event.
     * Set the instance of the application log resource into the internal property.
     * Enables the database profiler if the logDb flag in the configuration is set to true.
     * Activate the template debugging if the logTemplate flag in the configuration is set to true.
     * Register the listeners to log the controllers if the logController flag in the configuration is set to true.
     *
     * On Dispatch start activate db profiling
     *
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        if (!Shopware()->Bootstrap()->hasResource('Log')) {
            return;
        }

        $this->log = Shopware()->Log();

        if (!empty($this->Config()->logDb)) {
            Shopware()->Db()->getProfiler()->setEnabled(true);
        }

        if (!empty($this->Config()->logTemplate)) {
            Shopware()->Template()->setDebugging(true);
            Shopware()->Template()->debug_tpl = 'string:';
        }

        if (!empty($this->Config()->logController)) {
            Shopware()->Events()->registerSubscriber($this->getListeners());
        }

        if (!empty($this->Config()->logModel)) {
            Shopware()->ModelConfig();
            $this->modelLogger = new \Doctrine\DBAL\Logging\DebugStack();
            Shopware()->ModelConfig()->setSQLLogger($this->modelLogger);
        }
    }

    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collect sql performance results, template results and controller results
     * and dump to log component.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(Enlight_Event_EventArgs $args)
    {
        if ($this->log === null) {
            return;
        }

        if (!empty($this->Config()->logDb)) {
            $this->logDb();
        }

        if (!empty($this->Config()->logTemplate)) {
            $this->logTemplate();
        }

        if (!empty($this->Config()->logController)) {
            $this->logController();
        }

        if (!empty($this->Config()->logModel)) {
            $this->logModel();
        }
    }

    /**
     * Logs all database process to the internal log object.
     * Iterates all queries of the query profiler and writes the query,
     * the parameter and the elapsed seconds for the query into a new row of the log.
     *
     * @return void
     */
    public function logDb()
    {
        /** @var $profiler Zend_Db_Profiler */
        $profiler = Shopware()->Db()->getProfiler();

        $rows = array(array('time', 'count', 'sql', 'params'));
        $counts = array(10000);
        $total_time = 0;
        $queryProfiles = $profiler->getQueryProfiles();

        if (!$queryProfiles) {
            return;
        }

        /** @var $query Zend_Db_Profiler_Query */
        foreach ($queryProfiles as $query) {
            $id = md5($query->getQuery());
            $total_time += $query->getElapsedSecs();
            if (!isset($rows[$id])) {
                $rows[$id] = array(
                    number_format($query->getElapsedSecs(), 5, '.', ''),
                    1,
                    $query->getQuery(),
                    $query->getQueryParams()
                );
                $counts[$id] = $query->getElapsedSecs();
            } else {
                $rows[$id][1]++;
                $counts[$id] += $query->getElapsedSecs();
                $rows[$id][0] = number_format($counts[$id], 5, '.', '');
            }
        }

        array_multisort($counts, SORT_NUMERIC, SORT_DESC, $rows);
        $rows = array_values($rows);
        $total_time = round($total_time, 5);
        $total_count = $profiler->getTotalNumQueries();

        $label = "Database Querys ($total_count @ $total_time sec)";
        $table = array($label, $rows);
        $this->log->table($table);
    }

    /**
     * Logs all database process to the internal log object.
     * Iterates all queries of the query profiler and writes the query,
     * the parameter and the elapsed seconds for the query into a new row of the log.
     *
     * @return void
     */
    public function logModel()
    {
        $rows = array(array('time', 'count', 'sql', 'params'));
        $counts = array(10000);
        $totalTime = 0;
        $queries = $this->modelLogger->queries;

        if (empty($queries)) {
            return;
        }

        /** @var $query Zend_Db_Profiler_Query */
        foreach ($queries as $query) {
            $id = md5($query['sql']);
            $totalTime += $query['executionMS'];
            if (!isset($rows[$id])) {
                $rows[$id] = array(
                    number_format($query['executionMS'], 5, '.', ''),
                    1,
                    $query['sql'],
                    $query['params']
                );
                $counts[$id] = $query['executionMS'];
            } else {
                $rows[$id][1]++;
                $counts[$id] += $query['executionMS'];;
                $rows[$id][0] = number_format($counts[$id], 5, '.', '');
            }
        }

        //array_multisort($counts, SORT_NUMERIC, SORT_DESC, $rows);
        $rows = array_values($rows);
        $totalTime = round($totalTime, 5);
        $totalCount = count($queries);

        $label = "Model Querys ($totalCount @ $totalTime sec)";
        $table = array($label, $rows);
        $this->log->table($table);
    }


    /**
     * Logs all rendered templates into the internal log object.
     * Each logged template contains the template name, the required compile time,
     * the required render time and the required cache time.
     *
     * @return void
     */
    public function logTemplate()
    {
        $rows = array(array('name', 'compile_time', 'render_time', 'cache_time'));
        $total_time = 0;
        foreach (Smarty_Internal_Debug::$template_data as $template_file) {
            $total_time += $template_file['render_time'];
            $total_time += $template_file['cache_time'];
            $template_file['name'] = str_replace($this->Application()->CorePath(), '', $template_file['name']);
            $template_file['name'] = str_replace($this->Application()->AppPath(), '', $template_file['name']);
            $template_file['compile_time'] = $this->formatTime($template_file['compile_time']);
            $template_file['render_time'] = $this->formatTime($template_file['render_time']);
            $template_file['cache_time'] = $this->formatTime($template_file['cache_time']);
            unset($template_file['start_time']);
            $rows[] = array_values($template_file);
        }
        $total_time = round($total_time, 5);
        $total_count = count($rows) - 1;
        $label = "Benchmark Template ($total_count @ $total_time sec)";
        $table = array($label, $rows);
        $this->log->table($table);
    }

    /**
     * Get total execution time in controller
     *
     * @return void
     */
    public function logController()
    {
        $total_time = $this->formatTime(microtime(true) - $this->startTime);
        $label = "Benchmark Controller ($total_time sec)";
        $table = array($label, $this->results);
        $this->log->table($table);
    }

    /**
     * Logs all controller events into the internal log object.
     * Each logged events contains the event name, the execution time and the allocated peak of memory.
     *
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onBenchmarkEvent(Enlight_Event_EventArgs $args)
    {
        if (empty($this->results)) {
            $this->results[] = array('name', 'memory', 'time');
            $this->startTime = microtime(true);
            $this->startMemory = memory_get_peak_usage(true);
        }

        $this->results[] = array(
            0 => str_replace('Enlight_Controller_', '', $args->getName()),
            1 => $this->formatMemory(memory_get_peak_usage(true) - $this->startMemory),
            2 => $this->formatTime(microtime(true) - $this->startTime)
        );
    }

    /**
     * Monitor execution time and memory on specified event points in application
     *
     * @return Enlight_Event_Subscriber_Array
     */
    public function getListeners()
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

        $listeners = new Enlight_Event_Subscriber_Array();
        foreach ($events as $event) {
            $listeners->registerListener(
                new Enlight_Event_Handler_Default(
                    $event, array($this, 'onBenchmarkEvent'), -99
                )
            );
        }
        return $listeners;
    }

    /**
     * Format memory in a proper way
     *
     * @param  $size
     * @return string
     */
    public static function formatMemory($size)
    {
        if (empty($size)) {
            return '0.00 b';
        }
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @number_format($size / pow(1024, ($i = floor(log($size, 1024)))), 2, '.', '') . ' ' . $unit[$i];
    }

    /**
     * Format time for human readable
     *
     * @param  $time
     * @return string
     */
    public static function formatTime($time)
    {
        return number_format($time, 5, '.', '');
    }
}
