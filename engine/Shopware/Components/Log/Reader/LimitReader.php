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

/**
 * Class LimitReader
 *
 * @package Shopware\Components\Log\Reader
 */
class LimitReader implements ReaderInterface
{
    use ReaderTrait;

    /**
     * @var integer
     */
    private $offset;

    /**
     * @var null|integer
     */
    private $limit;

    public function __construct(ReaderInterface $iterator, $offset = 0, $limit = null)
    {
        $this->iterator = $iterator;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (!$this->iterator->valid()) {
            return false;
        }
        if (isset($this->limit)) {
            return $this->limit > $this->key();
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $count = $this->iterator->count() - $this->offset;
        if (isset($this->limit)) {
            if ($count > $this->limit) {
                return $this->limit;
            }
        }
        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->iterator->key() - $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($position)
    {
        $this->iterator->seek($this->offset + $position);
    }
}
