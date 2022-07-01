<?php

declare(strict_types=1);
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
use LimitIterator;
use SplFileObject;

class LogfileParser
{
    /**
     * @param string   $file
     * @param int|null $offset
     * @param int|null $limit
     * @param bool     $reverse
     *
     * @return array<array{data?: string, channel?: string, level?: string, message?: string, context?: string, extra?: string, raw: string}>
     */
    public function parseLogFile($file, $offset = null, $limit = null, $reverse = false)
    {
        if ($reverse) {
            $lineGenerator = LineReader::readLinesBackwards($file);
        } else {
            $lineGenerator = LineReader::readLines($file);
        }

        if (!\is_int($offset)) {
            $offset = 0;
        }
        if (!\is_int($limit)) {
            $limit = -1;
        }

        $reader = new LimitIterator($lineGenerator, $offset, $limit);

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
        $file = new SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);

        return $file->key();
    }

    /**
     * @return array{data?: string, channel?: string, level?: string, message?: string, context?: string, extra?: string, raw: string}
     */
    private function parseLine(string $log): array
    {
        $pattern = '/\[(?P<date>[^\[]*)\] (?P<channel>\w+).(?P<level>\w+): (?P<message>[^\[{]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';

        preg_match($pattern, $log, $data);

        if (!isset($data['date'])) {
            return [
                'raw' => $log,
            ];
        }

        return [
            'date' => (string) $data['date'],
            'channel' => (string) $data['channel'],
            'level' => (string) $data['level'],
            'message' => (string) $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true),
            'raw' => $log,
        ];
    }
}
