<?php

namespace Shopware\Tests\Service\Search\Facet;

use Enlight_Components_Test_TestCase;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;
use Shopware\Tests\Service\TestCase;

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
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 20),
                'third'  => array('attr1' => null)
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array($facet)
        );

        $this->assertCount(1, $result->getFacets());
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult', $result->getFacets()[0]);
    }

    public function testEmptyMode()
    {
        $facet = new ProductAttributeFacet(
            'attr1',
            ProductAttributeFacet::MODE_EMPTY
        );

        $result = $this->search(
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 22),
                'third'  => array('attr1' => null),
                'fourth' => array('attr1' => null)
            ),
            array('first', 'second', 'third', 'fourth'),
            null,
            array(),
            array($facet)
        );

        $this->assertCount(1, $result->getFacets());
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult', $result->getFacets()[0]);
    }

    public function testValuesMode()
    {
        $facet = new ProductAttributeFacet(
            'attr1',
            ProductAttributeFacet::MODE_VALUES
        );

        $result = $this->search(
            array(
                'first'  => array('attr1' => 10),
                'second' => array('attr1' => 10),
                'third'  => array('attr1' => null),
                'fourth'  => array('attr1' => null),
                'fifth' => array('attr1' => 10),
            ),
            array('first', 'second', 'third', 'fourth', 'fifth'),
            null,
            array(),
            array($facet)
        );

        $this->assertCount(1, $result->getFacets());

        /**@var $facet ValueListFacetResultInterface*/
        $facet = $result->getFacets()[0];
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult', $facet);

        $this->assertNull($facet->getValues()[0]->getId());
        $this->assertNull($facet->getValues()[0]->getLabel());

        $this->assertEquals(10, $facet->getValues()[1]->getId());
        $this->assertEquals(10, $facet->getValues()[1]->getLabel());
    }
}
