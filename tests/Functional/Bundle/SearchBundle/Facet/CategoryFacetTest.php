<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeItem;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class CategoryFacetTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
        return parent::getProduct($number, $context, $additionally);
    }

    public function testSingleProductInFacet()
    {
        $baseCategory = $this->helper->createCategory(array(
            'name' => 'firstLevel'
        ));

        $subCategory = $this->helper->createCategory(array(
            'name' => 'secondLevel',
            'parent' => $baseCategory->getId()
        ));

        $result = $this->search(
            array(
                'first' => $baseCategory,
                'second' => $subCategory,
                'third' => $subCategory,
                'fourth' => null
            ),
            array('first', 'second', 'third'),
            $baseCategory,
            array(),
            array(new CategoryFacet())
        );

        $this->assertCount(1, $result->getFacets());

        $facet = $result->getFacets();
        $facet = $facet[0];

        /**@var $facet TreeFacetResult*/
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult', $facet);

        $this->assertCount(1, $facet->getValues());

        /** @var TreeItem $value */
        $value = $facet->getValues()[0];
        $this->assertEquals('firstLevel', $value->getLabel());
    }

    public function testMultipleCategories()
    {
        $baseCategory = $this->helper->createCategory(array(
            'name' => 'firstLevel'
        ));

        $subCategory1 = $this->helper->createCategory(array(
            'name' => 'secondLevel-1',
            'parent' => $baseCategory->getId()
        ));
        $subCategory2 = $this->helper->createCategory(array(
            'name' => 'secondLevel-2',
            'parent' => $baseCategory->getId()
        ));

        $result = $this->search(
            array(
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory2,
                'fifth' => $subCategory2
            ),
            array('first', 'second', 'third', 'fourth', 'fifth'),
            $baseCategory,
            array(),
            array(new CategoryFacet())
        );

        $facet = $result->getFacets();
        $facet = $facet[0];

        /**@var $facet TreeFacetResult*/
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult', $facet);

        $this->assertCount(1, $facet->getValues());

        $value = $facet->getValues()[0];
        $this->assertEquals('firstLevel', $value->getLabel());
        $this->assertTrue($value->isActive());

        $this->assertEquals('secondLevel-1', $value->getValues()[0]->getLabel());
        $this->assertEquals('secondLevel-2', $value->getValues()[1]->getLabel());
    }

    public function testNestedCategories()
    {
        $baseCategory = $this->helper->createCategory(array(
            'name' => 'firstLevel'
        ));

        $subCategory1 = $this->helper->createCategory(array(
            'name' => 'secondLevel-1',
            'parent' => $baseCategory->getId()
        ));

        $subCategory2 = $this->helper->createCategory(array(
            'name' => 'thirdLevel-2',
            'parent' => $subCategory1->getId()
        ));

        $subCategory3 = $this->helper->createCategory(array(
            'name' => 'secondLevel-2',
            'parent' => $baseCategory->getId()
        ));

        /** @var \Shopware_Components_Config $config */
        $config = Shopware()->Container()->get('config');
        $config->offsetSet('categoryFilterDepth', 4);

        $result = $this->search(
            array(
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory3,
                'fifth' => $subCategory3
            ),
            array('first', 'second', 'third'),
            $subCategory1,
            array(),
            array(new CategoryFacet())
        );

        $facet = $result->getFacets();
        $facet = $facet[0];

        /**@var $facet TreeFacetResult*/
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult', $facet);

        $this->assertCount(1, $facet->getValues());

        /** @var TreeItem $value */
        $value = $facet->getValues()[0];
        $this->assertEquals('firstLevel', $value->getLabel());

        $value = $value->getValues()[0];
        $this->assertEquals('secondLevel-1', $value->getLabel());
        $this->assertTrue($value->isActive());

        $value = $value->getValues()[0];
        $this->assertEquals('thirdLevel-2', $value->getLabel());
    }
}
