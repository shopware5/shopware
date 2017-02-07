<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Tax;

use Shopware\Bundle\CartBundle\Domain\Tax\TaxRule;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;

class TaxRuleCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionIsCountable()
    {
        $collection = new TaxRuleCollection();
        static::assertCount(0, $collection);
    }

    public function testCountReturnsCorrectValue()
    {
        $collection = new TaxRuleCollection([
            new TaxRule(19),
            new TaxRule(18),
            new TaxRule(17)
        ]);
        static::assertCount(3, $collection);
    }

    public function testTaxRateIsUsedAsUniqueIdentifier()
    {
        $collection = new TaxRuleCollection([
            new TaxRule(19),
            new TaxRule(19),
            new TaxRule(19)
        ]);

        static::assertEquals(
            new TaxRuleCollection([new TaxRule(19)]),
            $collection
        );
    }

    public function testElementCanBeAccessedByTaxRate()
    {
        $collection = new TaxRuleCollection([
            new TaxRule(19),
            new TaxRule(18),
            new TaxRule(17)
        ]);
        static::assertEquals(
            new TaxRule(19),
            $collection->get(19)
        );
    }

    public function testTaxRateCanBeAddedToCollection()
    {
        $collection = new TaxRuleCollection();
        $collection->add(new TaxRule(19));

        static::assertEquals(
            new TaxRuleCollection([new TaxRule(19)]),
            $collection
        );
    }

    public function testCollectionCanBeCleared()
    {
        $collection = new TaxRuleCollection([
            new TaxRule(19),
            new TaxRule(18),
            new TaxRule(17)
        ]);
        $collection->clear();

        static::assertEquals(new TaxRuleCollection(), $collection);
    }

    public function testCollectionCanBeFilledWithMultipleElements()
    {
        $collection = new TaxRuleCollection();
        $collection->fill([
            new TaxRule(19),
            new TaxRule(18),
            new TaxRule(17)
        ]);

        static::assertEquals(
            new TaxRuleCollection([
                new TaxRule(19),
                new TaxRule(18),
                new TaxRule(17)
            ]),
            $collection
        );
    }

    public function testMergeFunctionReturnsNewInstance()
    {
        $a = new TaxRuleCollection([new TaxRule(19)]);
        $b = new TaxRuleCollection([new TaxRule(18)]);
        $c = $a->merge($b);

        static::assertNotSame($c, $a);
        static::assertNotSame($c, $b);
    }

    public function testMergeFunctionMergesAllTaxRules()
    {
        $a = new TaxRuleCollection([new TaxRule(19)]);
        $b = new TaxRuleCollection([new TaxRule(18)]);
        $c = $a->merge($b);

        static::assertEquals(
            new TaxRuleCollection([
                new TaxRule(19),
                new TaxRule(18)
            ]),
            $c
        );
    }

    public function testTaxRuleCanBeRemovedByRate()
    {
        $collection = new TaxRuleCollection([
            new TaxRule(19),
            new TaxRule(18),
            new TaxRule(17)
        ]);
        $collection->remove(19);
        static::assertEquals(
            new TaxRuleCollection([
                new TaxRule(18),
                new TaxRule(17)
            ]),
            $collection
        );
    }

    public function testGetOnEmptyCollection()
    {
        $collection = new TaxRuleCollection([]);
        static::assertNull($collection->get(19));
    }
}
