<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Recovery\Common\Archive;

use Countable;
use SeekableIterator;

abstract class Adapter implements SeekableIterator, Countable
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $count;

    /**
     * @param int $position
     */
    public function seek($position)
    {
        $this->position = (int) $position;
    }

    public function count()
    {
        return $this->count;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->count > $this->position;
    }

    /**
     * @return array|bool
     */
    public function each()
    {
        if (!$this->valid()) {
            return false;
        }
        $result = [$this->key(), $this->current()];
        $this->next();

        return $result;
    }
}
