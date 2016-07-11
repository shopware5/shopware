<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\ImmediateDeliveryCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ImmediateDeliveryConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @param array $data
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $data = array('inStock' => 0, 'minPurchase' => 1)
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['lastStock'] = true;
        $product['mainDetail'] = array_merge($product['mainDetail'], $data);

        return $product;
    }

    public function testNoStock()
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            array(
                'first'  => array('inStock' => 0, 'minPurchase' => 1),
                'second' => array('inStock' => 0, 'minPurchase' => 1),
                'third'  => array('inStock' => 2, 'minPurchase' => 1),
                'fourth' => array('inStock' => 1, 'minPurchase' => 1)
            ),
            array('third', 'fourth'),
            null,
            array($condition)
        );
    }

    public function testMinPurchaseEquals()
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            array(
                'first'  => array('inStock' => 0, 'minPurchase' => 1),
                'second' => array('inStock' => 0, 'minPurchase' => 1),
                'third'  => array('inStock' => 3, 'minPurchase' => 3),
                'fourth' => array('inStock' => 20, 'minPurchase' => 20)
            ),
            array('third', 'fourth'),
            null,
            array($condition)
        );
    }

    public function testSubVariantWithStock()
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            array(
                'first'  => array('inStock' => 0, 'minPurchase' => 1),
                'second' => array('inStock' => 0, 'minPurchase' => 1),
                'third'  => array('inStock' => 1, 'minPurchase' => 1),
                'fourth' => array('createVariants' => true)
            ),
            array('third', 'fourth'),
            null,
            array($condition)
        );
    }


    protected function createProduct(
        $number,
        ShopContext $context,
        Category $category,
        $additionally
    ) {
        if ($additionally['createVariants'] == true) {
            $fourth = $this->getProduct('fourth', $context, $category);
            $configurator = $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                'fourth'
            );

            $fourth = array_merge($fourth, $configurator);
            foreach ($fourth['variants'] as &$variant) {
                $variant['inStock'] = 4;
                $variant['minPurchase'] = 3;
            }
            return $this->helper->createArticle($fourth);
        } else {
            return parent::createProduct(
                $number,
                $context,
                $category,
                $additionally
            );
        }
    }
}
