<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class PropertyFacetTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $properties = array()
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product = array_merge($product, $properties);

        return $product;
    }

    public function testPropertyFacet()
    {
        $properties = $this->helper->getProperties(2, 3);

        $firstCombination = $this->createPropertyCombination(
            $properties,
            array(0, 1, 2)
        );

        $secondCombination = $this->createPropertyCombination(
            $properties,
            array(1, 2, 3)
        );

        $thirdCombination = $this->createPropertyCombination(
            $properties,
            array(2, 3, 4, 5)
        );

        $result = $this->search(
            array(
                'first' => $firstCombination,
                'second' => $secondCombination,
                'third' => $thirdCombination,
                'fourth' => array()
            ),
            array('first', 'second', 'third', 'fourth'),
            null,
            array(),
            array(new PropertyFacet())
        );

        $this->assertCount(1, $result->getFacets());

        /**@var $facet FacetResultGroup*/
        $facet = $result->getFacets()[0];
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup', $facet);

        $this->assertCount(2, $facet->getFacetResults());
        foreach ($facet->getFacetResults() as $result) {
            /**@var $result ValueListFacetResult*/
            $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult', $result);
            $this->assertCount(3, $result->getValues());
        }
    }

    public function testMultiplePropertySets()
    {
        $properties = $this->helper->getProperties(2, 3);
        $first = $this->createPropertyCombination($properties, array(0, 1, 2));
        $second = $this->createPropertyCombination($properties, array(3, 4, 5));

        $properties = $this->helper->getProperties(2, 3, 'PHP');
        $third = $this->createPropertyCombination($properties, array(0, 1, 2));
        $fourth = $this->createPropertyCombination($properties, array(3, 4, 5));


        $result = $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second', 'third', 'fourth'),
            null,
            array(),
            array(new PropertyFacet())
        );

        $this->assertCount(1, $result->getFacets());

        /**@var $facet FacetResultGroup*/
        foreach ($result->getFacets() as $facet) {
            $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\FacetResultGroup', $facet);

            $this->assertCount(4, $facet->getFacetResults());
            foreach ($facet->getFacetResults() as $result) {
                /**@var $result ValueListFacetResult*/
                $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult', $result);
                $this->assertCount(3, $result->getValues());
            }
        }
    }

    private function createPropertyCombination($properties, $indexes)
    {
        $combination = $properties;
        unset($combination['all']);

        $values = array();
        foreach ($properties['propertyValues'] as $index => $value) {
            if (in_array($index, $indexes)) {
                $values[] = $value;
            }
        }
        $combination['propertyValues'] = $values;
        return $combination;
    }
}
