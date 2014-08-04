<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceFacetTest extends TestCase
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

    public function testFacetWithCurrentCustomerGroupPrices()
    {
        $facet = new PriceFacet();
        $context = $this->getContext(true, null);
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            $context,
            $facet,
            array(
                'first'  => array($customerGroup->getKey() => 20, $fallback->getKey() => 1),
                'second' => array($customerGroup->getKey() => 10, $fallback->getKey() => 1),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 1),
                'fourth' => array($customerGroup->getKey() => 14, $fallback->getKey() => 1)
            ),
            array('second', 'third', 'fourth', 'first')
        );

        $this->assertEquals(110.00, $facet->getMinPrice());
        $this->assertEquals(120.00, $facet->getMaxPrice());
    }


    public function testFacetWithFallbackCustomerGroupPrices()
    {
        $facet = new PriceFacet();
        $context = $this->getContext(true, null);
        $fallback = $context->getFallbackCustomerGroup();

        $this->search(
            $context,
            $facet,
            array(
                'first'  => array($fallback->getKey() => 30),
                'second' => array($fallback->getKey() => 5),
                'third'  => array($fallback->getKey() => 12),
                'fourth' => array($fallback->getKey() => 14)
            ),
            array('second', 'third', 'fourth', 'first')
        );

        $this->assertEquals(105.00, $facet->getMinPrice());
        $this->assertEquals(130.00, $facet->getMaxPrice());
    }

    public function testFacetWithMixedCustomerGroupPrices()
    {
        $facet = new PriceFacet();
        $context = $this->getContext(true, null);
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            $context,
            $facet,
            array(
                'first'  => array($customerGroup->getKey() => 0, $fallback->getKey() => 5),
                'second' => array($fallback->getKey() => 50),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 14),
                'fourth' => array($fallback->getKey() => 12)
            ),
            array('second', 'third', 'fourth', 'first')
        );

        $this->assertEquals(100.00, $facet->getMinPrice());
        $this->assertEquals(150.00, $facet->getMaxPrice());
    }

    private function search(
        Context $context,
        PriceFacet $facet,
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
        $criteria->addFacet($facet);

        $result = Shopware()->Container()->get('product_number_search_dbal')
            ->search($criteria, $context);

        $this->assertSearchResult($result, $expectedNumbers);

        return $result;
    }

}
