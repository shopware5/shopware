<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\ProductContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ImmediateDeliveryFacetTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param ProductContext $context
     * @param array $data
     * @return array
     */
    protected function getProduct(
        $number,
        ProductContext $context,
        Category $category = null,
        $data = array('inStock' => 0, 'minPurchase' => 1)
    ) {
        $product = parent::getProduct($number, $context, $category);

        $product['lastStock'] = true;
        $product['mainDetail'] = array_merge($product['mainDetail'], $data);

        return $product;
    }

    public function testFacetWithNoStock()
    {
        $result = $this->search(
            array(
                'first'  => array('inStock' => 10),
                'second' => array('inStock' => 0),
                'third'  => array('inStock' => 10),
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array(new ImmediateDeliveryFacet())
        );
        $facet = $result->getFacets()[0];
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult', $facet);
    }

    public function testFacetWithMinPurchase()
    {
        $result = $this->search(
            array(
                'first'  => array('inStock' => 2, 'minPurchase' => 2),
                'second' => array('inStock' => 4, 'minPurchase' => 5),
                'third'  => array('inStock' => 3, 'minPurchase' => 2),
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array(new ImmediateDeliveryFacet())
        );
        $facet = $result->getFacets()[0];
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult', $facet);
    }

    public function testFacetWithNoData()
    {
        $result = $this->search(
            array(
                'first'  => array('inStock' => 1, 'minPurchase' => 2),
                'second' => array('inStock' => 1, 'minPurchase' => 4),
                'third'  => array('inStock' => 1, 'minPurchase' => 3),
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array(new ImmediateDeliveryFacet())
        );
        $this->assertCount(0, $result->getFacets());
    }
}
