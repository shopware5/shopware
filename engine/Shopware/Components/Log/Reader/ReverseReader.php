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

class ReverseReader implements ReaderInterface
{
    use ReaderTrait;

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $seek = $this->iterator->key();
        if ($seek > 0) {
            --$seek;
        } else {
            // seek to EOF, because -1 is not valid
            $seek = $this->count();
        }
        $this->iterator->seek($seek);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $count = $this->count();
        $key = $this->iterator->key();

        if ($count > $key) {
            return $count - 1 - $key;
        } else {
            return $count;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->seek($this->count() - 1);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        $count = $this->count();
        if ($position > $count) {
            $seek = $position - 1 - $count;
        } else {
            $seek = $count - 1 - $position;
        }
        $this->iterator->seek($seek);
    }
}
