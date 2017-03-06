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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle\Struct;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\StoreFrontBundle\Struct\Collection;

class CollectionTest extends TestCase
{
    public function testRemoveReturnsElement()
    {
        $collection = new Collection([1, 2, 3]);

        $this->assertSame(
            1,
            $collection->remove(0)
        );

        $this->assertSame(
            null,
            $collection->remove(100)
        );
    }

    public function testRemoveElement()
    {
        $collection = new Collection([1, 2, 3]);
        $collection->remove(0);

        $this->assertSame(
            [2, 3],
            $collection->getValues()
        );

        $collection->offsetUnset(1);
        $this->assertSame(
            [3],
            $collection->getValues()
        );
    }

    public function testOffsetExists()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertTrue($collection->offsetExists(0));
        $this->assertFalse($collection->offsetExists(3));
    }

    public function testGet()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertSame(1, $collection->get(0));
        $this->assertSame(1, $collection->offsetGet(0));
    }

    public function testSet()
    {
        $collection = new Collection([1, 2, 3]);

        $collection->set(0, 100);
        $this->assertSame(
            [100, 2, 3],
            $collection->getValues()
        );

        $collection->offsetSet(0, 200);
        $this->assertSame(
            [200, 2, 3],
            $collection->getValues()
        );

        $collection->offsetSet(null, 100);
        $this->assertSame(
            [200, 2, 3, 100],
            $collection->getValues()
        );
    }

    public function testGetKeys()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertSame(
            [0, 1, 2],
            $collection->getKeys()
        );
    }

    public function testCount()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertCount(3, $collection);
        $this->assertSame(3, $collection->count());
    }

    public function testIsEmpty()
    {
        $this->assertFalse((new Collection([1, 2, 3]))->isEmpty());
        $this->assertTrue((new Collection([]))->isEmpty());
    }

    public function testFilter()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            new Collection([1]),
            $collection->filter(function ($element) {
                return $element <= 1;
            })
        );
    }

    public function testFilterCreatesNoNewKeys()
    {
        $collection = new Collection([1, 2, 3]);
        $new = new Collection([]);
        $new->set(1, 2);
        $new->set(2, 3);

        $this->assertEquals(
            $new,
            $collection->filter(function ($element) {
                return $element > 1;
            })
        );
    }

    public function testMap()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertEquals(
            new Collection([2, 4, 6]),
            $collection->map(function ($i) {
                return $i * 2;
            })
        );
    }

    public function testExists()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertTrue(
            $collection->exists(function ($i) {
                return $i === 1;
            })
        );
        $this->assertFalse(
            $collection->exists(function ($i) {
                return $i === 4;
            })
        );
    }

    public function testClear()
    {
        $collection = new Collection([1, 2, 3]);
        $collection->clear();

        $this->assertEquals(
            new Collection([]),
            $collection
        );
    }

    public function testJsonSerializable()
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertSame(
            json_encode([1, 2, 3]),
            json_encode($collection)
        );
    }

    public function testAdd()
    {
        $collection = new Collection([1, 2, 3]);
        $collection->add(4);
        $this->assertEquals(
            new Collection([1, 2, 3, 4]),
            $collection
        );
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf(\ArrayIterator::class, (new Collection([]))->getIterator());
    }

    public function testCollectionIsIterable()
    {
        $this->assertTrue(is_iterable(new Collection([])));
    }
}
