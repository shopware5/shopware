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

namespace Shopware\Recovery\Update\Steps;

use Shopware\Recovery\Common\DumpIterator;

class SnippetStep
{
    private $conn;

    private $dumper;

    public function __construct(\PDO $connection, DumpIterator $dumper)
    {
        $this->conn = $connection;
        $this->dumper = $dumper;
    }

    public function run($offset)
    {
        $conn = $this->conn;
        $dump = $this->dumper;

        $totalCount = $dump->count();

        $preSql = '
        SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
        SET time_zone = "+00:00";
        SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = "de_DE");
        SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = "en_GB");
        ';
        $conn->exec($preSql);
        $dump->seek($offset);

        $startTime = microtime(true);
        $sql = [];
        $count = 0;
        while ($dump->valid() && ++$count) {
            $current = trim($dump->current());
            if (empty($current)) {
                $dump->next();
                continue;
            }

            $sql[] = $current;

            if ($count % 50 === 0) {
                try {
                    $conn->exec(implode(";\n", $sql));
                    $sql = [];
                } catch (\PDOException $e) {
                    return new ErrorResult($e->getMessage(), $e, ['query' => $sql]);
                }
            }

            $dump->next();
            if ($count > 5000 || ceil(microtime(true) - $startTime) > 5) {
                break;
            }
        }

        if (!empty($sql)) {
            try {
                $conn->exec(implode(";\n", $sql));
            } catch (\PDOException $e) {
                return new ErrorResult('second' . $e->getMessage(), $e, ['query' => $sql]);
            }
        }

        if ($dump->valid()) {
            return new ValidResult($dump->key(), $totalCount);
        }

        return new FinishResult($dump->key(), $totalCount);
    }
}
