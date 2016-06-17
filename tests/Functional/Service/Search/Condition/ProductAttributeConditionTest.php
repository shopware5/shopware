<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ProductAttributeConditionTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $attribute = array('attr1' => 10)
    ) {
        $product = parent::getProduct($number, $context, $category);
        $product['mainDetail']['attribute'] = $attribute;

        return $product;
    }

    public function testEquals()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            10
        );

        $this->search(
            array(
                'First-Match' => array('attr1' => 10),
                'Not-Match'   => array('attr1' => 20),
            ),
            array('First-Match'),
            null,
            array($condition)
        );
    }

    public function testContains()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_CONTAINS,
            'Rot'
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => 'Dunkel-Rot'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => 'Grün'),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }

    public function testEndsWith()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_ENDS_WITH,
            'Grün'
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot-Grün'),
                'Not-Match'    => array('attr1' => 'Grün-Rot'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }

    public function testStartsWith()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_STARTS_WITH,
            'Grün'
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Grün-Rot'),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }

    public function testInOperator()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_IN,
            array('Grün', 'Rot')
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }

    public function testNull()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            null
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => null),
                'Second-Match' => array('attr1' => null),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }

    public function testNotNull()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_NEQ,
            null
        );

        $this->search(
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => null),
                'Not-Match2'   => array('attr1' => null),
            ),
            array('First-Match', 'Second-Match'),
            null,
            array($condition)
        );
    }
}
