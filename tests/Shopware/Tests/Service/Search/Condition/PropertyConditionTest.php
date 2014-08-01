<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Search\TestCase;

class PropertyConditionTest extends TestCase
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

    public function testSingleProperty()
    {
        $properties = $this->helper->getProperties(2, 3);
        $values = $properties['propertyValues'];

        $firstCombination = $this->createPropertyCombination(
            $properties,
            array(0, 1)
        );

        $secondCombination = $this->createPropertyCombination(
            $properties,
            array(1, 2)
        );

        $thirdCombination = $this->createPropertyCombination(
            $properties,
            array(2, 3)
        );

        $condition = new PropertyCondition(array($values[1]['id']));

        $this->search(
            $condition,
            array(
                'first' => $firstCombination,
                'second' => $secondCombination,
                'third' => $thirdCombination,
                'fourth' => array()
            ),
            array('first', 'second')
        );
    }

    public function testMultipleProperties()
    {
        $properties = $this->helper->getProperties(2, 3);
        $values = $properties['propertyValues'];

        $firstCombination = $this->createPropertyCombination(
            $properties,
            array(0, 1, 5)
        );

        $secondCombination = $this->createPropertyCombination(
            $properties,
            array(1, 2, 3, 4)
        );

        $thirdCombination = $this->createPropertyCombination(
            $properties,
            array(2, 3, 5)
        );

        $fourth = $this->createPropertyCombination(
            $properties,
            array(1, 2, 4)
        );

        $condition = new PropertyCondition(array(
            $values[2]['id'],
            $values[3]['id']
        ));

        $this->search(
            $condition,
            array(
                'first' => $firstCombination,
                'second' => $secondCombination,
                'third' => $thirdCombination,
                'fourth' => $fourth
            ),
            array('second', 'third')
        );
    }

    private function createPropertyCombination($properties, $indexes)
    {
        $combination = $properties;
        unset($combination['all']);

        $values = array();
        foreach($properties['propertyValues'] as $index => $value) {
            if (in_array($index, $indexes)) {
                $values[] = $value;
            }
        }
        $combination['propertyValues'] = $values;
        return $combination;
    }

    private function search(
        PropertyCondition $condition,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $properties) {
            $data = $this->getProduct($number, $context, $category, $properties);
            $this->helper->createArticle($data);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addCondition($condition);

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);
    }

}
