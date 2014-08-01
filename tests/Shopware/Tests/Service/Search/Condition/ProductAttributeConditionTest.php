<?php

namespace Shopware\Tests\Service\Search\Condition;

use Enlight_Components_Test_TestCase;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Converter;
use Shopware\Tests\Service\Helper;
use Shopware\Tests\Service\Search\TestCase;

class ProductAttributeConditionTest extends TestCase
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

    public function testEquals()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            10
        );

        $this->search(
            $condition,
            array(
                'First-Match' => array('attr1' => 10),
                'Not-Match'   => array('attr1' => 20),
            ),
            array('First-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => 'Dunkel-Rot'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => 'Grün'),
            ),
            array('First-Match', 'Second-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot-Grün'),
                'Not-Match'    => array('attr1' => 'Grün-Rot'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Grün-Rot'),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => null),
                'Second-Match' => array('attr1' => null),
                'Not-Match'    => array('attr1' => 'Rot-Grün'),
                'Not-Match2'   => array('attr1' => 'Dunkel-Rot'),
            ),
            array('First-Match', 'Second-Match')
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
            $condition,
            array(
                'First-Match'  => array('attr1' => 'Grün'),
                'Second-Match' => array('attr1' => 'Rot'),
                'Not-Match'    => array('attr1' => null),
                'Not-Match2'   => array('attr1' => null),
            ),
            array('First-Match', 'Second-Match')
        );
    }

    private function search(ProductAttributeCondition $condition, $products, $expectedNumbers)
    {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $attribute) {
            $data = $this->getProduct($number, $context, $category, $attribute);
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
