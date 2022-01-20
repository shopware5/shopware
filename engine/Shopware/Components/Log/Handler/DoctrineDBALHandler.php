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

namespace Shopware\Components\Log\Handler;

use DateTime;
use Doctrine\DBAL\Connection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

/**
 * @deprecated in 5.6, will be removed in 5.8 without replacement
 *
 * @phpstan-import-type Level from \Monolog\Logger
 * @phpstan-import-type LevelName from \Monolog\Logger
 */
class DoctrineDBALHandler extends AbstractProcessingHandler
{
    /**
     * @var Connection
     */
    protected $conn;

    /**
     * @var array
     */
    protected $columnMap = [];

    /**
     * @var string
     */
    protected $table;

    /**
     * @param string     $table
     * @param int|string $level
     * @phpstan-param Level|LevelName|LogLevel::* $level
     *
     * @param bool $bubble
     */
    public function __construct(Connection $conn, $table, array $columnMap, $level = Logger::DEBUG, $bubble = true)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->columnMap = $columnMap;

        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        if ($this->columnMap === null) {
            $dataToInsert = $record;
        } else {
            $dataToInsert = [];
            foreach ($this->columnMap as $columnName => $fieldKey) {
                if (isset($record[$fieldKey])) {
                    $dataToInsert[$this->conn->quoteIdentifier($columnName)] = $record[$fieldKey];
                }
            }
        }

        array_walk_recursive($dataToInsert, function (&$value) {
            // Convert DateTime instances to ISO-8601 Strings
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ISO8601);
            }
        });

        $this->conn->insert($this->table, $dataToInsert);
    }
}
