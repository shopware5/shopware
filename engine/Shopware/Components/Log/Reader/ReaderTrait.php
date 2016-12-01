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

trait ReaderTrait
{
    /**
     * @param \SeekableIterator|\Countable $iterator
     */
    public function __construct(\SeekableIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @var integer
     */
    private $count;

    /**
     * @var \SeekableIterator
     */
    protected $iterator;

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        $this->iterator->seek($position);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (!isset($this->count)) {
            if ($this->iterator instanceof \Countable) {
                $this->count = $this->iterator->count();
            } else {
                $this->count = $this->calculateCount();
            }
        }
        return $this->count;
    }

    private function calculateCount()
    {
        $start = $this->iterator->key();
        $count = $start;
        while ($this->valid()) {
            $this->iterator->next();
            ++$count;
        }
        $this->iterator->seek($start);
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getInnerIterator()
    {
        return $this->iterator;
    }
}