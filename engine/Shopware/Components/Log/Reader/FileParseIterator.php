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

namespace Shopware\Components\Log\Reader;

class FileParseIterator extends \SplFileObject implements \SeekableIterator
{
    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = parent::current();
        if (!$current) {
            return false;
        }
        return $this->parse($current);
    }

    private function parse($log)
    {
        $pattern = '/\[(?P<date>[^\[]*)\] (?P<channel>\w+).(?P<level>\w+): (?P<message>[^\[]+) (?P<context>[\[\{].*[\]\}]) (?P<extra>[\[\{].*[\]\}])/';

        preg_match($pattern, $log, $data);

        if (!isset($data['date'])) {
            return [
                'raw' => $log
            ];
        }

        return [
            'date' => $data['date'],
            'channel' => $data['channel'],
            'level' => $data['level'],
            'message' => $data['message'],
            'context' => json_decode($data['context'], true),
            'extra' => json_decode($data['extra'], true),
            'raw' => $log
        ];
    }

    /**
     * Not not an empty line
     *
     * @return bool true if not an empty line, false otherwise.
     */
    public function valid()
    {
        return (bool)parent::current();
    }
}
