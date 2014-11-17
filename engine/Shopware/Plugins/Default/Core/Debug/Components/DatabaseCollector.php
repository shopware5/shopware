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
class DatabaseCollector implements CollectorInterface
{
    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(\Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->db = $db;
    }

    /**
     *
     */
    public function start()
    {
        $this->db->getProfiler()->setEnabled(true);
    }

    /**
     * Logs all database process to the internal log object.
     * Iterates all queries of the query profiler and writes the query,
     * the parameter and the elapsed seconds for the query into a new row of the log.
     */
    public function logResults(Logger $log)
    {
        /** @var $profiler \Zend_Db_Profiler */
        $profiler = $this->db->getProfiler();

        $rows = array(array('time', 'count', 'sql', 'params'));
        $counts = array(10000);
        $total_time = 0;
        $queryProfiles = $profiler->getQueryProfiles();

        if (!$queryProfiles) {
            return;
        }

        /** @var $query \Zend_Db_Profiler_Query */
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

        $log->table($table);
    }
}
