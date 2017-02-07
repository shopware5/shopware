<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\Price\Price;
use Shopware\Bundle\CartBundle\Domain\Price\PriceCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTax;
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;

class PriceCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionIsCountable()
    {
        $collection = new PriceCollection();
        static::assertCount(0, $collection);
    }

    public function testCountReturnsCorrectValue()
    {
        $collection = new PriceCollection([
            new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new Price(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection())
        ]);
        static::assertCount(3, $collection);
    }

    public function testAddFunctionAddsAPrice()
    {
        $collection = new PriceCollection();
        $collection->add(new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()));

        static::assertEquals(
            new PriceCollection([
                new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection())
            ]),
            $collection
        );
    }

    public function testFillFunctionFillsTheCollection()
    {
        $collection = new PriceCollection();
        $collection->fill([
            new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection())
        ]);

        static::assertEquals(
            new PriceCollection([
                new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection()),
                new Price(10, 10, new CalculatedTaxCollection(), new TaxRuleCollection())
            ]),
            $collection
        );
    }

    public function testTotalAmountWithEmptyCollection()
    {
        $collection = new PriceCollection();
        static::assertSame(0, $collection->getTotalPrice()->getPrice());
    }

    public function testTotalAmountWithMultiplePrices()
    {
        $collection = new PriceCollection([
            new Price(200, 200, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new Price(300, 300, new CalculatedTaxCollection(), new TaxRuleCollection()),
        ]);
        static::assertSame(500, $collection->getTotalPrice()->getPrice());
    }

    public function testGetTaxesReturnsACalculatedTaxCollection()
    {
        $collection = new PriceCollection();
        static::assertEquals(new CalculatedTaxCollection(), $collection->getCalculatedTaxes());
    }

    public function testGetTaxesReturnsCollectionWithAllTaxes()
    {
        $collection = new PriceCollection([
            new Price(
                200,
                200,
                new CalculatedTaxCollection([
                    new CalculatedTax(1, 15, 1),
                    new CalculatedTax(2, 16, 1),
                    new CalculatedTax(3, 17, 1)
                ]),
                new TaxRuleCollection()
            ),
            new Price(
                300,
                300,
                new CalculatedTaxCollection([
                    new CalculatedTax(4, 19, 1),
                    new CalculatedTax(5, 20, 1),
                    new CalculatedTax(6, 21, 1)
                ]),
                new TaxRuleCollection()
            )
        ]);

        static::assertEquals(
            new CalculatedTaxCollection([
                new CalculatedTax(1, 15, 1),
                new CalculatedTax(2, 16, 1),
                new CalculatedTax(3, 17, 1),
                new CalculatedTax(4, 19, 1),
                new CalculatedTax(5, 20, 1),
                new CalculatedTax(6, 21, 1)
            ]),
            $collection->getCalculatedTaxes()
        );
    }

    public function testClearFunctionRemovesAllPrices()
    {
        $collection = new PriceCollection([
            new Price(200, 200, new CalculatedTaxCollection(), new TaxRuleCollection()),
            new Price(300, 300, new CalculatedTaxCollection(), new TaxRuleCollection()),
        ]);

        $collection->clear();
        static::assertEquals(new PriceCollection(), $collection);
    }
}
