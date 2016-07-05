<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Facet\PriceFacet;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class PriceFacetTest extends TestCase
{
    protected function getTestContext($displayGross, $discount = null)
    {
        $context = parent::getContext();

        $data = array('key' => 'BAK', 'tax' => $displayGross);

        $context->setFallbackCustomerGroup(
            $this->converter->convertCustomerGroup($this->helper->createCustomerGroup($data))
        );

        $context->getCurrentCustomerGroup()->setDisplayGrossPrices($displayGross);
        $context->getCurrentCustomerGroup()->setUseDiscount(($discount !== null));
        $context->getCurrentCustomerGroup()->setPercentageDiscount($discount);

        return $context;
    }

    /**
     * @param $number
     * @param ShopContext $context
     * @param \Shopware\Models\Category\Category $category
     * @param array $prices
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
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
        $context = $this->getTestContext(true, null);
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            array(
                'first'  => array($customerGroup->getKey() => 20, $fallback->getKey() => 1),
                'second' => array($customerGroup->getKey() => 10, $fallback->getKey() => 1),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 1),
                'fourth' => array($customerGroup->getKey() => 14, $fallback->getKey() => 1)
            ),
            array('second', 'third', 'fourth', 'first'),
            null,
            array(),
            array(new PriceFacet()),
            array(),
            $context
        );

        /**@var $facet RangeFacetResult */
        $facet = $result->getFacets()[0];
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult', $facet);

        $this->assertEquals(110.00, $facet->getMin());
        $this->assertEquals(120.00, $facet->getMax());
    }

    public function testFacetWithFallbackCustomerGroupPrices()
    {
        $context = $this->getTestContext(true, null);
        $context->setFallbackCustomerGroup($this->getEkCustomerGroup());
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            array(
                'first'  => array($fallback->getKey() => 30),
                'second' => array($fallback->getKey() => 5),
                'third'  => array($fallback->getKey() => 12),
                'fourth' => array($fallback->getKey() => 14)
            ),
            array('second', 'third', 'fourth', 'first'),
            null,
            array(),
            array(new PriceFacet()),
            array(),
            $context
        );

        /**@var $facet RangeFacetResult*/
        $facet = $result->getFacets()[0];

        $this->assertEquals(105.00, $facet->getMin());
        $this->assertEquals(130.00, $facet->getMax());
    }

    /**
     * @group skipElasticSearch
     */
    public function testFacetWithMixedCustomerGroupPrices()
    {
        $context = $this->getTestContext(true, null);
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $result = $this->search(
            array(
                'first'  => array($customerGroup->getKey() => 0, $fallback->getKey() => 5),
                'second' => array($fallback->getKey() => 50),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 14),
                'fourth' => array($fallback->getKey() => 12)
            ),
            array('second', 'third', 'fourth', 'first'),
            null,
            array(),
            array(new PriceFacet()),
            array(),
            $context
        );
        /**@var $facet RangeFacetResult*/
        $facet = $result->getFacets()[0];

        $this->assertEquals(100.00, $facet->getMin());
        $this->assertEquals(150.00, $facet->getMax());
    }

    /**
     * @group skipElasticSearch
     */
    public function testFacetWithCurrencyFactor()
    {
        $context = $this->getTestContext(true, null);
        $customerGroup = $context->getCurrentCustomerGroup();
        $fallback = $context->getFallbackCustomerGroup();

        $context->getCurrency()->setFactor(2.5);

        $result = $this->search(
            array(
                'first'  => array($customerGroup->getKey() => 0, $fallback->getKey() => 5),
                'second' => array($fallback->getKey() => 50),
                'third'  => array($customerGroup->getKey() => 12, $fallback->getKey() => 14),
                'fourth' => array($fallback->getKey() => 12)
            ),
            array('second', 'third', 'fourth', 'first'),
            null,
            array(),
            array(new PriceFacet()),
            array(),
            $context
        );
        /**@var $facet RangeFacetResult*/
        $facet = $result->getFacets()[0];

        $this->assertEquals(250.00, $facet->getMin());
        $this->assertEquals(375.00, $facet->getMax());
    }
}
