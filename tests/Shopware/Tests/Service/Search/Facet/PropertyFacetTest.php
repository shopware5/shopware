<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\PropertyFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Property\Set;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PropertyFacetTest extends TestCase
{
    protected function getProduct(
        $number,
        Context $context,
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
        $values = $properties['propertyValues'];

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

        $facet = new PropertyFacet();

        $result = $this->search(
            $facet,
            array(
                'first' => $firstCombination,
                'second' => $secondCombination,
                'third' => $thirdCombination,
                'fourth' => array()
            ),
            array('first', 'second', 'third', 'fourth')
        );

        $this->assertCount(1, $result->getFacets());

        $this->assertCount(1, $facet->getProperties());

        /**@var $set Set*/
        $set = array_shift($facet->getProperties());

        $this->assertCount(2, $set->getGroups());
        foreach ($set->getGroups() as $group) {

            $this->assertCount(3, $group->getOptions());

            foreach ($group->getOptions() as $option) {

                $this->assertTrue($option->hasAttribute('facet'));
                $attribute = $option->getAttribute('facet');

                switch ($option->getId()) {
                    case $values[0]['id']:
                        $this->assertEquals(1, $attribute->get('total'));
                        break;

                    case $values[1]['id']:
                        $this->assertEquals(2, $attribute->get('total'));
                        break;

                    case $values[2]['id']:
                        $this->assertEquals(3, $attribute->get('total'));
                        break;

                    case $values[3]['id']:
                        $this->assertEquals(2, $attribute->get('total'));
                        break;

                    case $values[4]['id']:
                        $this->assertEquals(1, $attribute->get('total'));
                        break;

                    case $values[5]['id']:
                        $this->assertEquals(1, $attribute->get('total'));
                        break;
                }
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

    /**
     * @param PropertyFacet $facet
     * @param $products
     * @param $expectedNumbers
     * @return \Shopware\Bundle\SearchBundle\ProductNumberSearchResult
     */
    private function search(
        PropertyFacet $facet,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach ($products as $number => $properties) {
            $data = $this->getProduct($number, $context, $category, $properties);
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
