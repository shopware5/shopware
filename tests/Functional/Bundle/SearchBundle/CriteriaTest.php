<?php

namespace Shopware\Tests\Bundle\SearchBundle;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\SearchBundle\Sorting\ProductNameSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Tests\Bundle\StoreFrontBundle\TestCase;

class CriteriaTest extends TestCase
{
    public function testUniqueCondition()
    {
        $criteria = new Criteria();

        $criteria->addCondition(new CategoryCondition(array(1)));
        $criteria->addCondition(new CategoryCondition(array(3)));
        $this->assertCount(1, $criteria->getConditions());
    }

    public function testUniqueFacet()
    {
        $criteria = new Criteria();
        $criteria->addFacet(new PriceFacet());
        $criteria->addFacet(new PriceFacet());
        $this->assertCount(1, $criteria->getFacets());
    }

    public function testUniqueSorting()
    {
        $criteria = new Criteria();
        $criteria->addSorting(new PriceSorting());
        $criteria->addSorting(new PriceSorting());
        $this->assertCount(1, $criteria->getSortings());
    }

    public function testIndexedSorting()
    {
        /** @var SortingInterface[] $sortings */
        $sortings = array(
            new PriceSorting(),
            new ProductNameSorting(),
            new PopularitySorting()
        );

        $criteria = new Criteria();
        foreach ($sortings as $sort) {
            $criteria->addSorting($sort);
        }

        foreach ($sortings as $expected) {
            $sorting = $criteria->getSorting($expected->getName());
            $this->assertEquals($expected, $sorting);
        }
    }

    public function testConditionOverwrite()
    {
        $criteria = new Criteria();

        $criteria->addCondition(new CategoryCondition(array(1)));

        $condition = new CategoryCondition(array(3));
        $criteria->addCondition($condition);

        $this->assertCount(1, $criteria->getConditions());
        $condition = $criteria->getCondition($condition->getName());

        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\Condition\CategoryCondition', $condition);

        /** @var CategoryCondition $condition */
        $this->assertEquals(array(3), $condition->getCategoryIds());
    }
}
