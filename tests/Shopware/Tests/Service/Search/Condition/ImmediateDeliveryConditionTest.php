<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Helper;
use Shopware\Tests\Service\Search\TestCase;

class ImmediateDeliveryConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param Context $context
     * @param array $data
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
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
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testNoStock-1', $context, $category),
            $this->getProduct('testNoStock-2', $context, $category),
            $this->getProduct('testNoStock-3', $context, $category, array('inStock' => 2, 'minPurchase' => 1)),
            $this->getProduct('testNoStock-4', $context, $category, array('inStock' => 2, 'minPurchase' => 1)),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addImmediateDeliveryCondition();


        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testNoStock-3', 'testNoStock-4')
        );
    }

    public function testMinPurchaseEquals()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testMinPurchaseEquals-1', $context, $category),
            $this->getProduct('testMinPurchaseEquals-2', $context, $category),
            $this->getProduct('testMinPurchaseEquals-3', $context, $category, array('inStock' => 3, 'minPurchase' => 3)),
            $this->getProduct('testMinPurchaseEquals-4', $context, $category, array('inStock' => 20, 'minPurchase' => 20)),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addImmediateDeliveryCondition();

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMinPurchaseEquals-3', 'testMinPurchaseEquals-4')
        );
    }
}
