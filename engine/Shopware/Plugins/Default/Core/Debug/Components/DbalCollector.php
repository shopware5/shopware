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

use Doctrine\DBAL\Logging\DebugStack;
use Shopware\Components\Logger;
use Shopware\Components\Model\Configuration;

class DbalCollector implements CollectorInterface
{
    /**
     * @var Configuration
     */
    protected $modelConfig;

    /**
     * @var
     */
    protected $modelLogger;

    public function __construct(Configuration $modelConfig)
    {
        $this->modelConfig = $modelConfig;
    }

    public function start()
    {
        $this->modelLogger = new DebugStack();
        $this->modelConfig->setSQLLogger($this->modelLogger);
    }

    /**
     * Logs all database process to the internal log object.
     * Iterates all queries of the query profiler and writes the query,
     * the parameter and the elapsed seconds for the query into a new row of the log.
     */
    public function logResults(Logger $log)
    {
        $rows = [['time', 'count', 'sql', 'params']];
        $counts = [10000];
        $totalTime = 0;
        $queries = $this->modelLogger->queries;

        if (empty($queries)) {
            return;
        }

        /** @var \Zend_Db_Profiler_Query $query */
        foreach ($queries as $query) {
            $id = md5($query['sql']);
            $totalTime += $query['executionMS'];
            if (!isset($rows[$id])) {
                $rows[$id] = [
                        number_format($query['executionMS'], 5, '.', ''),
                        1,
                        $query['sql'],
                        $query['params'],
                ];
                $counts[$id] = $query['executionMS'];
            } else {
                ++$rows[$id][1];
                $counts[$id] += $query['executionMS'];
                $rows[$id][0] = number_format($counts[$id], 5, '.', '');
            }
        }

        $rows = array_values($rows);
        $totalTime = round($totalTime, 5);
        $totalCount = count($queries);

        $label = "Model Querys ($totalCount @ $totalTime sec)";
        $table = [$label, $rows];

        $log->table($table);
    }
}
