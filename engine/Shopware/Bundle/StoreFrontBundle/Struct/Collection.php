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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;

class Collection implements Countable, IteratorAggregate, ArrayAccess, \JsonSerializable
{
    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @param array $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @param string|int $key
     *
     * @return mixed|null
     */
    public function remove($key)
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * Required by interface ArrayAccess.
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]) || array_key_exists($offset, $this->elements);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        if (!isset($offset)) {
            $this->add($value);

            return;
        }
        $this->set($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * @param mixed $offset
     *
     * @return mixed|null|void
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->elements[$key]) ? $this->elements[$key] : null;
    }

    /**
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->elements);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @param int|string $key
     * @param mixed      $value
     */
    public function set($key, $value)
    {
        $this->elements[$key] = $value;
    }

    /**
     * @param mixed $element
     */
    public function add($element): void
    {
        $this->elements[] = $element;
    }

    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function map(Closure $func): Collection
    {
        return $this->createFrom(array_map($func, $this->elements));
    }

    public function filter(Closure $p): Collection
    {
        return $this->createFrom(array_filter($this->elements, $p));
    }

    public function exists(Closure $p): bool
    {
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * resets the collection elements
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $elements elements
     *
     * @return Collection
     */
    protected function createFrom(array $elements): Collection
    {
        return new static($elements);
    }
}
