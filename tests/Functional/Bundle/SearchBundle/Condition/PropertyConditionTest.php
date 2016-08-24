<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\PropertyCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class PropertyConditionTest extends TestCase
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


    public function testSinglePropertyConditionWithOneValue()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(1, 5));
        $third  = $this->createPropertyCombination($properties, array(2, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 7));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id']
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first'),
            null,
            $conditions
        );
    }

    public function testSinglePropertyConditionWithTwoValues()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(1, 5));
        $third  = $this->createPropertyCombination($properties, array(2, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 7));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id'],
            $values[1]['id']
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second'),
            null,
            $conditions
        );
    }

    public function testSinglePropertyConditionWithThreeValues()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(1, 5));
        $third  = $this->createPropertyCombination($properties, array(2, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 7));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id'],
            $values[1]['id'],
            $values[3]['id'],
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second', 'fourth'),
            null,
            $conditions
        );
    }


    public function testTwoPropertyConditionsWithOneValue()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(0, 4));
        $third  = $this->createPropertyCombination($properties, array(2, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 7));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id']
        ));

        $conditions[] = new PropertyCondition(array(
            $values[4]['id']
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second'),
            null,
            $conditions
        );
    }

    public function testTwoPropertyConditionsWithTwoValues()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(1, 5));
        $third  = $this->createPropertyCombination($properties, array(1, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 5));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id'],
            $values[1]['id']
        ));

        $conditions[] = new PropertyCondition(array(
            $values[4]['id'],
            $values[5]['id'],
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second'),
            null,
            $conditions
        );
    }


    public function testTwoPropertyConditionsWithThreeValues()
    {
        $properties = $this->helper->getProperties(3, 4);
        $values = $properties['propertyValues'];

        /*
         * Group 0:   0, 1, 2, 3
         * Group 1:   4, 5, 6, 7
         * Group 2:   8, 9, 10, 11
         */

        $first  = $this->createPropertyCombination($properties, array(0, 4));
        $second = $this->createPropertyCombination($properties, array(1, 5));
        $third  = $this->createPropertyCombination($properties, array(2, 6));
        $fourth = $this->createPropertyCombination($properties, array(3, 5));

        $conditions = array();

        $conditions[] = new PropertyCondition(array(
            $values[0]['id'],
            $values[1]['id'],
            $values[2]['id']
        ));

        $conditions[] = new PropertyCondition(array(
            $values[4]['id'],
            $values[5]['id'],
            $values[6]['id']
        ));

        $this->search(
            array(
                'first' => $first,
                'second' => $second,
                'third' => $third,
                'fourth' => $fourth
            ),
            array('first', 'second', 'third'),
            null,
            $conditions
        );
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
