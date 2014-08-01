<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Tests\Service\Search\TestCase;

class CategoryConditionTest extends TestCase
{
    public function testSingleCategory()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testSingleCategory-1', $context, $category),
            $this->getProduct('testSingleCategory-2', $context, $category),
            $this->getProduct('testSingleCategory-3', $context, $category),
            $this->getProduct('testSingleCategory-4', $context, null),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleCategory-1', 'testSingleCategory-2', 'testSingleCategory-3')
        );
    }

    public function testMultipleCategories()
    {
        $category = $this->helper->createCategory();
        $second = $this->helper->createCategory(array('name' => 'Multiple-Categories-Test'));
        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testMultipleCategories-1', $context, $category),
            $this->getProduct('testMultipleCategories-2', $context, $category),
            $this->getProduct('testMultipleCategories-3', $context, $second),
            $this->getProduct('testMultipleCategories-4', $context, $second)
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId(), $second->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMultipleCategories-1', 'testMultipleCategories-2', 'testMultipleCategories-3', 'testMultipleCategories-4')
        );
    }
}
