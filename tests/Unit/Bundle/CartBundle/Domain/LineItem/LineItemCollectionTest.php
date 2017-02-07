<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\LineItem;

use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;

class LineItemCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionIsCountable()
    {
        $collection = new LineItemCollection();
        static::assertCount(0, $collection);
    }

    public function testCountReturnsCorrectValue()
    {
        $collection = new LineItemCollection([
            new LineItem('A', '', 1),
            new LineItem('B', '', 1),
            new LineItem('C', '', 1),
        ]);
        static::assertCount(3, $collection);
    }

    public function testCollectionOverwriteExistingIdentifierWithLastItem()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('A', 'a', 2),
            new LineItem('A', 'a', 3)
        ]);

        static::assertEquals(
            new LineItemCollection([
                new LineItem('A', 'a', 3)
            ]),
            $collection
        );
    }

    public function testFilterReturnsNewCollectionWithCorrectItems()
    {
        $collection = new LineItemCollection([
            new LineItem('A1', 'A', 1),
            new LineItem('A2', 'A', 1),
            new LineItem('B', 'B', 1),
            new LineItem('B2', 'B', 1),
            new LineItem('B3', 'B', 1),
            new LineItem('B4', 'B', 1),
            new LineItem('C', 'C', 1)
        ]);

        static::assertEquals(
            new LineItemCollection([
                new LineItem('A1', 'A', 1),
                new LineItem('A2', 'A', 1)
            ]),
            $collection->filterType('A')
        );
        static::assertEquals(
            new LineItemCollection([
                new LineItem('B', 'B', 1),
                new LineItem('B2', 'B', 1),
                new LineItem('B3', 'B', 1),
                new LineItem('B4', 'B', 1)
            ]),
            $collection->filterType('B')
        );
        static::assertEquals(
            new LineItemCollection([
                new LineItem('C', 'C', 1)
            ]),
            $collection->filterType('C')
        );

        static::assertEquals(
            new LineItemCollection(),
            $collection->filterType('NOT EXISTS')
        );
    }

    public function testFilterReturnsCollection()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);

        static::assertInstanceOf(LineItemCollection::class, $collection->filterType('a'));
    }

    public function testFilterReturnsNewCollection()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);

        static::assertNotSame($collection, $collection->filterType('a'));
    }

    public function testLineItemsCanBeCleared()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);
        $collection->clear();
        static::assertEquals(new LineItemCollection(), $collection);
    }

    public function testLineItemsCanBeRemovedByIdentifier()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);
        $collection->remove('A');

        static::assertEquals(new LineItemCollection([
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]), $collection);
    }

    public function testIdentifiersCanEasyAccessed()
    {
        $collection = new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);

        static::assertSame([
            'A', 'B', 'C'
        ], $collection->getIdentifiers());
    }

    public function testFillCollectionWithItems()
    {
        $collection = new LineItemCollection();
        $collection->fill([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]);

        static::assertEquals(new LineItemCollection([
            new LineItem('A', 'a', 1),
            new LineItem('B', 'a', 1),
            new LineItem('C', 'a', 1),
        ]), $collection);
    }

    public function testGetOnEmptyCollection()
    {
        $collection = new LineItemCollection();
        static::assertNull($collection->get('not found'));
    }
}
