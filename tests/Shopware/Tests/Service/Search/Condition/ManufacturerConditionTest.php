<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ManufacturerConditionTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param Supplier $manufacturer
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        Supplier $manufacturer = null
    ) {
        $product = parent::getProduct($number, $context, $category);

        if ($manufacturer) {
            $product['supplierId'] = $manufacturer->getId();
        }

        return $product;
    }

    public function testSingleManufacturer()
    {
        $category = $this->helper->createCategory();
        $manufacturer = $this->helper->createManufacturer();

        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testSingleManufacturer-1', $context, $category, null),
            $this->getProduct('testSingleManufacturer-2', $context, $category, $manufacturer),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addManufacturerCondition(array($manufacturer->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testSingleManufacturer-2')
        );
    }

    public function testMultipleManufacturers()
    {
        $category = $this->helper->createCategory();
        $manufacturer = $this->helper->createManufacturer();
        $second = $this->helper->createManufacturer();

        $context = $this->getContext();

        $articles = array(
            $this->getProduct('testMultipleManufacturers-1', $context, $category, null),
            $this->getProduct('testMultipleManufacturers-2', $context, $category, $manufacturer),
            $this->getProduct('testMultipleManufacturers-3', $context, $category, $second),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addManufacturerCondition(array($manufacturer->getId(), $second->getId()));

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testMultipleManufacturers-2', 'testMultipleManufacturers-3')
        );
    }
}
