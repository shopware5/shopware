<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Search\TestCase;

class CategoryFacetTest extends TestCase
{

    public function testSingleProductInFacet()
    {
        $baseCategory = $this->helper->createCategory(array(
            'name' => 'firstLevel'
        ));

        $subCategory = $this->helper->createCategory(array(
            'name' => 'secondLevel',
            'parent' => $baseCategory->getId()
        ));

        $facet = new CategoryFacet();
        $this->search(
            $facet,
            $baseCategory,
            array(
                'first' => $baseCategory,
                'second' => $subCategory,
                'third' => $subCategory,
                'fourth' => null
            ),
            array('first', 'second', 'third')
        );

        $this->assertCount(1, $facet->getCategories());

        /**@var $category \Shopware\Bundle\StoreFrontBundle\Struct\Category*/
        $category = array_shift($facet->getCategories());

        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Category', $category);
        $this->assertTrue($category->hasAttribute('facet'));
        $this->assertEquals(2, $category->getAttribute('facet')->get('total'));
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

        $facet = new CategoryFacet();
        $this->search(
            $facet,
            $baseCategory,
            array(
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory2,
                'fifth' => $subCategory2
            ),
            array('first', 'second', 'third', 'fourth', 'fifth')
        );

        $this->assertCount(2, $facet->getCategories());

        foreach($facet->getCategories() as $category) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Category', $category);
            $this->assertTrue($category->hasAttribute('facet'));

            if ($category->getId() === $subCategory1->getId()) {
                $this->assertEquals(2, $category->getAttribute('facet')->get('total'));
            } else {
                $this->assertEquals(3, $category->getAttribute('facet')->get('total'));
            }
        }
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

        $facet = new CategoryFacet();
        $this->search(
            $facet,
            $baseCategory,
            array(
                'first' => $subCategory1,
                'second' => $subCategory1,
                'third' => $subCategory2,
                'fourth' => $subCategory3,
                'fifth' => $subCategory3
            ),
            array('first', 'second', 'third', 'fourth', 'fifth')
        );

        $this->assertCount(2, $facet->getCategories());

        foreach($facet->getCategories() as $category) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Category', $category);
            $this->assertTrue($category->hasAttribute('facet'));

            if ($category->getId() === $subCategory1->getId()) {
                $this->assertEquals(3, $category->getAttribute('facet')->get('total'));
            } else {
                $this->assertEquals(2, $category->getAttribute('facet')->get('total'));
            }
        }
    }

    /**
     * @param FacetInterface $facet
     * @param Category $category
     * @param $products
     * @param $expectedNumbers
     * @return \Shopware\Bundle\SearchBundle\ProductNumberSearchResult
     */
    private function search(
        FacetInterface $facet,
        Category $category,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();

        foreach($products as $number => $productCategory) {
            $data = $this->getProduct($number, $context, $productCategory);
            $this->helper->createArticle($data);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addFacet($facet);

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);

        return $result;
    }


}
