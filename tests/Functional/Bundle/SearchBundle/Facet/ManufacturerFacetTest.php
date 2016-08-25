<?php

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class ManufacturerFacetTest extends TestCase
{
    /**
     * @param $number
     * @param ShopContext $context
     * @param \Shopware\Models\Category\Category $category
     * @param Supplier $manufacturer
     * @return array
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $manufacturer = null
    ) {
        $product = parent::getProduct($number, $context, $category);

        if ($manufacturer) {
            $product['supplierId'] = $manufacturer->getId();
        } else {
            $product['supplierId'] = null;
        }

        return $product;
    }

    public function testWithNoManufacturer()
    {
        $result = $this->search(
            array(
                'first' => null,
                'second' => null
            ),
            array('first', 'second'),
            null,
            array(),
            array(new ManufacturerFacet())
        );

        $this->assertCount(0, $result->getFacets());
    }

    public function testSingleManufacturer()
    {
        $supplier = $this->helper->createManufacturer();

        $result = $this->search(
            array(
                'first' => $supplier,
                'second' => $supplier,
                'third' => null
            ),
            array('first', 'second', 'third'),
            null,
            array(),
            array(new ManufacturerFacet())
        );

        $facet = $result->getFacets()[0];

        /**@var $facet ValueListFacetResult*/
        $this->assertInstanceOf('Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult', $facet);

        $this->assertCount(1, $facet->getValues());
        $this->assertEquals($supplier->getId(), $facet->getValues()[0]->getId());
    }

    public function testMultipleManufacturers()
    {
        $supplier1 = $this->helper->createManufacturer();
        $supplier2 = $this->helper->createManufacturer(array(
            'name' => 'Test-Manufacturer-2'
        ));

        $result = $this->search(
            array(
                'first' => $supplier1,
                'second' => $supplier1,
                'third' => $supplier2,
                'fourth' => null
            ),
            array('first', 'second', 'third', 'fourth'),
            null,
            array(),
            array(new ManufacturerFacet())
        );

        /**@var $facet ValueListFacetResult*/

        $facet = $result->getFacets()[0];
        $this->assertCount(2, $facet->getValues());
    }
}
