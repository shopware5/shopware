<?php

namespace Shopware\Tests\Service\Search\Sorting;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\PriceSorting;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceSortingTest extends TestCase
{
    protected function getContext($displayGross, $discount = null)
    {
        $context = parent::getContext();

        $data = array('key' => 'BAK', 'tax' => $displayGross);

        $context->setFallbackCustomerGroup(
            $this->helper->createCustomerGroup($data)
        );

        $context->getCurrentCustomerGroup()->setDisplayGrossPrices($displayGross);
        $context->getCurrentCustomerGroup()->setUseDiscount(($discount !== null));
        $context->getCurrentCustomerGroup()->setPercentageDiscount($discount);

        return $context;
    }

    /**
     * @param $number
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param \Shopware\Models\Category\Category $category
     * @param array $prices
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        $prices = array()
    ) {
        $product = parent::getProduct($number, $context, $category);

        if (!empty($prices)) {
            $product['mainDetail']['prices'] = array();

            foreach ($prices as $key => $price) {
                $product['mainDetail']['prices'] = array_merge(
                    $product['mainDetail']['prices'],
                    $this->helper->getGraduatedPrices($key, $price)
                );
            }
        }
        return $product;
    }

    public function testCurrentCustomerGroupPriceSorting()
    {
        $sorting = new PriceSorting();
        $context = $this->getContext(true, 0);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            $context,
            $sorting,
            array(
                'first'  => array($customerGroup->getKey() => 20, $fallback->getKey() => 1),
                'second' => array($customerGroup->getKey() => 10, $fallback->getKey() => 1),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 1),
                'fourth' => array($customerGroup->getKey() => 14, $fallback->getKey() => 1)
            ),
            array('second', 'third', 'fourth', 'first')
        );
    }

    public function testFallbackCustomerGroupPriceSorting()
    {
        $sorting = new PriceSorting();
        $context = $this->getContext(true, 0);

        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            $context,
            $sorting,
            array(
                'first'  => array($fallback->getKey()  => 20),
                'second' => array($fallback->getKey()  => 10),
                'third'  => array($fallback->getKey()  => 12),
                'fourth' => array($fallback->getKey()  => 14)
            ),
            array('second', 'third', 'fourth', 'first')
        );
    }

    public function testFallbackAndCurrentCustomerGroupPriceSorting()
    {
        $sorting = new PriceSorting();
        $context = $this->getContext(true, 0);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            $context,
            $sorting,
            array(
                'first'  => array($customerGroup->getKey() => 20, $fallback->getKey() => 1),
                'second' => array($fallback->getKey() => 10),
                'third'  => array($fallback->getKey() => 12),
                'fourth' => array($customerGroup->getKey() => 14, $fallback->getKey() => 1)
            ),
            array('second', 'third', 'fourth', 'first')
        );
    }

    public function testCustomerGroupDiscount()
    {
        $sorting = new PriceSorting();
        $context = $this->getContext(true, 10);

        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            $context,
            $sorting,
            array(
                'first'  => array($customerGroup->getKey() => 40, $fallback->getKey() => 1),
                'second' => array($fallback->getKey() => 10),
                'third'  => array($fallback->getKey() => 20),
                'fourth' => array($customerGroup->getKey() => 30, $fallback->getKey() => 1)
            ),
            array('second', 'third', 'fourth', 'first')
        );

        $products = $result->getProducts();

        $product = $products['second'];
        $this->assertTrue($product->hasAttribute('search'));
        $this->assertEquals(99.00, $product->getAttribute('search')->get('cheapest_price'));

        $product = $products['third'];
        $this->assertTrue($product->hasAttribute('search'));
        $this->assertEquals(108.00, $product->getAttribute('search')->get('cheapest_price'));

        $product = $products['fourth'];
        $this->assertTrue($product->hasAttribute('search'));
        $this->assertEquals(117.00, $product->getAttribute('search')->get('cheapest_price'));

        $product = $products['first'];
        $this->assertTrue($product->hasAttribute('search'));
        $this->assertEquals(126.00, $product->getAttribute('search')->get('cheapest_price'));
    }

    private function search(
        Context $context,
        PopularitySorting $sorting,
        $products,
        $expectedNumbers
    ) {
        $category = $this->helper->createCategory();

        foreach ($products as $number => $prices) {
            $data = $this->getProduct($number, $context, $category, $prices);
            $this->helper->createArticle($data);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addSorting($sorting);

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);

        $this->assertSearchResultSorting($result, $expectedNumbers);

        return $result;
    }

}
