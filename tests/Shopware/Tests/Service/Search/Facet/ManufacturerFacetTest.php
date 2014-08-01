<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Search\TestCase;

class ManufacturerFacetTest extends TestCase
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
        } else {
            $product['supplierId'] = null;
        }

        return $product;
    }

    public function testWithNoManufacturer()
    {
        $facet = new ManufacturerFacet();
        $this->search(
            $facet,
            array(
                'first' => null,
                'second' => null
            ),
            array('first', 'second')
        );
        $this->assertCount(0, $facet->getManufacturers());
    }

    public function testSingleManufacturer()
    {
        $facet = new ManufacturerFacet();
        $supplier = $this->helper->createManufacturer();

        $this->search(
            $facet,
            array(
                'first' => $supplier,
                'second' => $supplier,
                'third' => null
            ),
            array('first', 'second', 'third')
        );
        $this->assertCount(1, $facet->getManufacturers());

        $manufacturer = array_shift($facet->getManufacturers());

        /**@var $manufacturer Manufacturer*/
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer', $manufacturer);
        $this->assertTrue($manufacturer->hasAttribute('facet'));
        $this->assertEquals(2, $manufacturer->getAttribute('facet')->get('total'));
    }

    public function testMultipleManufacturers()
    {
        $facet = new ManufacturerFacet();
        $supplier1 = $this->helper->createManufacturer();
        $supplier2 = $this->helper->createManufacturer(array(
            'name' => 'Test-Manufacturer-2'
        ));

        $this->search(
            $facet,
            array(
                'first' => $supplier1,
                'second' => $supplier1,
                'third' => $supplier2,
                'fourth' => null
            ),
            array('first', 'second', 'third', 'fourth')
        );

        $this->assertCount(2, $facet->getManufacturers());
        foreach($facet->getManufacturers() as $manufacturer) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer', $manufacturer);

            $this->assertTrue($manufacturer->hasAttribute('facet'));

            if ($manufacturer->getId() === $supplier1->getId()) {
                $this->assertEquals(2, $manufacturer->getAttribute('facet')->get('total'));
            } else {
                $this->assertEquals(1, $manufacturer->getAttribute('facet')->get('total'));
            }
        }
    }

    private function search(
        FacetInterface $facet,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $manufacturer) {
            $data = $this->getProduct($number, $context, $category, $manufacturer);
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
