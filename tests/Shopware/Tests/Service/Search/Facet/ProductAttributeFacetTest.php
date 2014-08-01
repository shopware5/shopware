<?php

namespace Shopware\Tests\Service\Search\Facet;

use Enlight_Components_Test_TestCase;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;
use Shopware\Tests\Service\Search\TestCase;

class ProductAttributeFacetTest extends TestCase
{
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        $attribute = array('attr1' => 10)
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product['mainDetail']['attribute'] = $attribute;

        return $product;
    }

    public function testNotEmptyMode()
    {
        $facet = new ProductAttributeFacet(
            'attr1',
            ProductAttributeFacet::MODE_NOT_EMPTY
        );

        $result = $this->search(
            $facet,
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 20),
                'third'  => array('attr1' => null)
            ),
            array('first', 'second', 'third')
        );

        $this->assertCount(1, $result->getFacets());

        $this->assertNotEmpty($facet->getResult());

        $result = $facet->getResult();
        $this->assertEquals(2, $result[0]['total']);
    }

    public function testEmptyMode()
    {
        $facet = new ProductAttributeFacet(
            'attr1',
            ProductAttributeFacet::MODE_EMPTY
        );

        $result = $this->search(
            $facet,
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 22),
                'third'  => array('attr1' => null),
                'fourth' => array('attr1' => null)
            ),
            array('first', 'second', 'third', 'fourth')
        );

        $this->assertCount(1, $result->getFacets());

        $this->assertNotEmpty($facet->getResult());

        $result = $facet->getResult();
        $this->assertEquals(2, $result[0]['total']);
    }

    public function testValuesMode()
    {
        $facet = new ProductAttributeFacet(
            'attr1',
            ProductAttributeFacet::MODE_VALUES
        );

        $result = $this->search(
            $facet,
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 10),
                'third'  => array('attr1' => null),
                'fourth'  => array('attr1' => null),
                'fifth' => array('attr1' => 10),
            ),
            array('first', 'second', 'third', 'fourth', 'fifth')
        );

        $this->assertCount(1, $result->getFacets());

        $this->assertNotEmpty($facet->getResult());
        $result = $facet->getResult();

        foreach($result as $value) {
            if ($value['attr1'] == null) {
                $this->assertEquals(2, $value['total']);
            } else {
                $this->assertEquals(3, $value['total']);
            }
        }
    }

    private function search(
        ProductAttributeFacet $facet,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $attribute) {
            $data = $this->getProduct($number, $context, $category, $attribute);
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
