<?php
/**
 * This script contains several functions that can be used to measure the
 * PHP performance and memory usage of a Shopware instance. You should configure
 * this script as an <b>auto_prepend_file</b> in your php.ini or as a php_value
 * in the httpd.conf or a .htaccess file.
 *
 * <b>php.ini</b>
 * <code>
 * auto_prepend_file = /path/to/prepend.php
 * </code>
 *
 * <b>php_value</b>
 * <code>
 * php_value auto_prepend_file /path/to/prepend.php
 * </code>
 *
 * This script accepts three different values that can be passed as value of
 * the GET parameter <b>__shopware_profile</b> to control  and read the contents
 * of the generated log file.
 *
 * <dl>
 *   <dt>http://example.com?__shopware_profile=reset</dt>
 *   <dd>Removes a previously created log file.</dd>
 *   <dt>http://example.com?__shopware_profile=raw</dt>
 *   <dd>Returns the raw log csv file.</dd>
 *   <dt>http://example.com?__shopware_profile=average</dt>
 *   <dd>Returns a csv file with average and statistical values.</dd>
 * </dl>
 */

/**
 * When did the execution of this shopware request start.
 */
$time = microtime(true);

/**
 * Used profiling log file.
 */
$file = dirname($_SERVER['SCRIPT_FILENAME']) . '/__shopware_profile.csv';

/**
 * Should we execute an administrative command or is it a regular request that
 * should write a new entry into the profiling log?
 */
if (isset($argv[1]) && __FILE__ === realpath($argv[0])) {
    __shopware_profile_download_average($argv[1]);
} else if (isset($_GET['__shopware_profile'])) {
    switch ($_GET['__shopware_profile']) {
        case 'reset':
            __shopware_profile_reset($file);
            break;

        case 'raw':
            __shopware_profile_download_raw($file);
            break;

        case 'average':
            __shopware_profile_download_average($file);
            break;
    }
} else {
    register_shutdown_function('__shopware_profile_on_shutdown', $file, $time);
}

/**
 * Hook function that records the execution time and the consumed memory in a
 * separate log file. We will register this function for php's shutdown event
 * so that this function is even called when the original script calls
 * <b>exit()</b> or <b>die()</b>.
 *
 * @param string $file Absolute path of the used log file.
 * @param float  $time Microtime measured at script start time.
 *
 * @return void
 */
function __shopware_profile_on_shutdown($file, $time)
{
    file_put_contents(
        $file,
        sprintf("%s,%s\n", (microtime(true) - $time), memory_get_peak_usage()),
        FILE_APPEND
    );
}

/**
 * This function removes a previously created log file. You should trigger this
 * function before a new performance test starts, so that you have a clean
 * environment.
 *
 * @param string $file Absolute path of the used log file.
 *
 * @return void
 */
function __shopware_profile_reset($file)
{
    if (file_exists($file)) {
        unlink($file);
    }
}

/**
 * This function sends the raw log file content to the calling client application.
 *
 * @param string $file Absolute path of the used log file.
 *
 * @return void
 */
function __shopware_profile_download_raw($file)
{
    if (file_exists($file))
    {
        header('Content-Type: text/plain; charset=UTF-8');
        echo file_get_contents($file);
    }
    exit(0);
}

/**
 * This function calculates some statistical values from the raw log file
 * entries. Then it takes these values and creates a simple csv file and sends
 * it to the calling client application.
 *
 * @param string $file Absolute path of the used log file.
 *
 * @return void
 */
function __shopware_profile_download_average($file)
{
    if (file_exists($file)) {
        $data = __shopware_profile_parse_log($file);

        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Average time (s),Max time (s),Min time (s),',
             "Average memory (Mb),Max memory (Mb),Min memory (Mb),Total requests\n",
             round($data['averageTime'], 4), ',',
             round($data['maximumTime'], 4), ',',
             round($data['minimumTime'], 4), ',',
             round($data['averageMemory'] / 1048576, 2), ',',
             round($data['maximumMemory'] / 1048576, 2), ',',
             round($data['minimumMemory'] / 1048576, 2), ',',
             $data['numberOfRequests'], "\n";
    }
    exit(0);
}

/**
 * This function parses the profiling log file and returns the following
 * aggregated statistics from the raw entries:
 *
 * <ul>
 *   <li>averageTime: Average execution time of all requests.</li>
 *   <li>maximumTime: Request with the longest execution time.</li>
 *   <li>minimumTime: Request with the shortest execution time.</li>
 *   <li>averageMemory: Average memory consumption of all requests.</li>
 *   <li>maximumMemory: Request with the greatest memory footprint.</li>
 *   <li>minimumMemory: Request with the smallest memory footprint.</li>
 *   <li>numberOfRequests: Total number of processed PHP requests.</li>
 * </ul>
 *
 * @param string $file Absolute path of the used log file.
 *
 * @return array
 */
function __shopware_profile_parse_log($file)
{
    $handle = fopen($file, 'r');

    $entryCount  = 0;

    $averageTime = 0;
    $maximumTime = 0;
    $minimumTime = PHP_INT_MAX;

    $averageMemory = 0;
    $maximumMemory = 0;
    $minimumMemory = PHP_INT_MAX;

    while ($data = fgetcsv($handle, 1024, ',')) {
        ++$entryCount;

        $averageTime += $data[0];
        $maximumTime  = max($maximumTime, $data[0]);
        $minimumTime  = min($minimumTime, $data[0]);

        $averageMemory += $data[1];
        $maximumMemory  = max($maximumMemory, $data[1]);
        $minimumMemory  = min($minimumMemory, $data[1]);
    }

    fclose($handle);

    return array(
        'averageTime'       =>  ($averageTime / $entryCount),
        'maximumTime'       =>  $maximumTime,
        'minimumTime'       =>  $minimumTime,
        'averageMemory'     =>  ($averageMemory / $entryCount),
        'maximumMemory'     =>  $maximumMemory,
        'minimumMemory'     =>  $minimumMemory,
        'numberOfRequests'  =>  $entryCount
    );
}