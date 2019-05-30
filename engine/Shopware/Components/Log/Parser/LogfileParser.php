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

namespace Shopware\Components\Log\Parser;

use Bcremer\LineReader\LineReader;

class LogfileParser
{
    /**
     * @param string   $file
     * @param int      $offset
     * @param int|null $limit
     * @param bool     $reverse
     *
     * @return array
     */
    public function parseLogFile($file, $offset = null, $limit = null, $reverse = false)
    {
        if ($reverse) {
            $lineGenerator = LineReader::readLinesBackwards($file);
        } else {
            $lineGenerator = LineReader::readLines($file);
        }

        $reader = new \LimitIterator($lineGenerator, $offset, $limit);

        $result = [];
        foreach ($reader as $line) {
            $result[] = $this->parseLine($line);
        }

        return $result;
    }

    /**
     * @param string $filePath
     *
     * @return int
     */
    public function countLogFile($filePath)
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }

    /**
     * @param string $log
     *
     * @return string[]
     */
    private function parseLine($log)
    {
        $pattern = '/\[(?P<date>[^\[]*)\] (?P<channel>\w+).(?P<level>\w+): (?P<message>[^\[{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';

        preg_match($pattern, $log, $data);

        if (!isset($data['date'])) {
            return [
                'raw' => $log,
            ];
        }

        return [
            'date' => $data['date'],
            'channel' => $data['channel'],
            'level' => $data['level'],
            'message' => $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true),
            'raw' => $log,
        ];
    }
}
