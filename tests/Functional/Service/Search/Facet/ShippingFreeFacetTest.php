<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ShippingFreeFacetTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ShopContext $context
     * @param bool $shippingFree
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
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
            array(
                'first' => true,
                'second' => false,
                'third' => true
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array($facet)
        );

        $this->assertCount(1, $result->getFacets());
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult', $result->getFacets()[0]);
    }


    public function testShippingFreeWithoutMatch()
    {
        $facet = new ShippingFreeFacet();
        $result = $this->search(
            array(
                'first' => false,
                'second' => false,
                'third' => false
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array($facet)
        );

        $this->assertCount(0, $result->getFacets());
    }
}
