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

class ReaderFactory
{
    /**
     * @param $file
     * @param int $offset
     * @param null|int $limit
     * @param bool $reverse
     * @return ReaderInterface
     */
    public function createFileReader($file, $offset = null, $limit = null, $reverse = false)
    {
        $iterator = new FileParseIterator($file, 'r');
        if ($reverse) {
            $reader = new ReverseReader($iterator);
        } else {
            $reader = new Reader($iterator);
        }
        if (isset($offset) || isset($limit)) {
            $reader = new LimitReader($reader, $offset, $limit);
        }
        return $reader;
    }
}
