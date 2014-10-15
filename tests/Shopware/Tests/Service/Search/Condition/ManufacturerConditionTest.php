<?php

namespace Shopware\Tests\Service\Search\Condition;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
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
        $manufacturer = $this->helper->createManufacturer();
        $condition = new ManufacturerCondition(array($manufacturer->getId()));

        $this->search(
            array(
                'first' => $manufacturer,
                'second' => $manufacturer,
                'third' => null
            ),
            array('first', 'second'),
            null,
            array($condition)
        );
    }

    public function testMultipleManufacturers()
    {
        $manufacturer = $this->helper->createManufacturer();
        $second = $this->helper->createManufacturer();

        $condition = new ManufacturerCondition(array(
            $manufacturer->getId(),
            $second->getId()
        ));

        $this->search(
            array(
                'first' => $manufacturer,
                'second' => $second,
                'third' => null
            ),
            array('first', 'second'),
            null,
            array($condition)
        );
    }
}
