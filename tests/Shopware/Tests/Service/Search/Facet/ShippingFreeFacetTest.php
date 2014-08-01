<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Search\TestCase;

class ShippingFreeFacetTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param bool $shippingFree
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
        Category $category = null,
        $shippingFree = true
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['shippingFree'] = $shippingFree;

        return $product;
    }

    public function testShippingFree()
    {
        $facet = new ShippingFreeFacet();
        $result = $this->search(
            $facet,
            array(
                'first' => true,
                'second' => false,
                'third' => true
            ),
            array('first', 'second', 'third')
        );

        $this->assertCount(1, $result->getFacets());

        $this->assertEquals(2, $facet->getTotal());
    }


    public function testShippingFreeWithoutMatch()
    {
        $facet = new ShippingFreeFacet();
        $result = $this->search(
            $facet,
            array(
                'first' => false,
                'second' => false,
                'third' => false
            ),
            array('first', 'second', 'third')
        );

        $this->assertCount(1, $result->getFacets());
        $this->assertEquals(0, $facet->getTotal());
    }

    /**
     * @param FacetInterface $facet
     * @param $products
     * @param $expectedNumbers
     * @return ProductNumberSearchResult
     */
    private function search(
        FacetInterface $facet,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $shippingFree) {
            $data = $this->getProduct($number, $context, $category, $shippingFree);
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
