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

namespace Shopware\Bundle\CartBundle\Domain;

use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate, \JsonSerializable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->clear();
        array_map([$this, 'add'], $items);
    }

    /**
     * @param array $items
     */
    public function fill(array $items)
    {
        array_map([$this, 'add'], $items);
    }

    /**
     * @param mixed $item
     */
    public function add($item)
    {
        $this->items[] = $item;
    }

    /**
     * @return string[]
     */
    public function keys()
    {
        return array_keys($this->items);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param $key
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
        }
    }

    /**
     * Removes all elements from the collection
     */
    public function clear()
    {
        $this->items = [];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param callable $fn
     * @return array
     */
    public function map(callable $fn)
    {
        return array_map($fn, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->items;
    }
}
