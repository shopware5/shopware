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

namespace Shopware\Bundle\CartBundle\Domain;

use ArrayIterator;

abstract class Collection implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->fill($elements);
    }

    public function fill(array $elements): void
    {
        array_map([$this, 'add'], $elements);
    }

    public function clear(): void
    {
        $this->elements = [];
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    public function map(\Closure $closure): array
    {
        return array_map($closure, $this->elements);
    }

    public function filterInstance(string $class): Collection
    {
        return $this->filter(function ($item) use ($class) {
            return $item instanceof $class;
        });
    }

    public function filter(\Closure $closure): Collection
    {
        return new static(array_filter($this->elements, $closure));
    }

    public function slice(int $offset, ?int $length = null): Collection
    {
        return new static(array_slice($this->elements, $offset, $length, true));
    }

    /**
     * Allows to use php-`foreach` to iterate over all elements inside the collection.
     * Allows to use php-`count` function to count elements inside the collection
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    protected function doAdd($element): void
    {
        $this->elements[] = $element;
    }

    protected function doRemoveByKey($key): void
    {
        unset($this->elements[$key]);
    }
}
