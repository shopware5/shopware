<?php

namespace Shopware\Tests\Service\Search\Facet;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ImmediateDeliveryFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Search\TestCase;

class ImmediateDeliveryFacetTest extends TestCase
{
    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param Context $context
     * @param array $data
     * @return array
     */
    protected function getProduct(
        $number,
        Context $context,
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
        $facet = new ImmediateDeliveryFacet();
        $this->search(
            $facet,
            array(
                'first'  => array('inStock' => 10),
                'second' => array('inStock' => 0),
                'third'  => array('inStock' => 10),
            ),
            array('first', 'second', 'third')
        );

        $this->assertEquals(2, $facet->getTotal());
    }

    public function testFacetWithMinPurchase()
    {
        $facet = new ImmediateDeliveryFacet();
        $this->search(
            $facet,
            array(
                'first'  => array('inStock' => 2, 'minPurchase' => 2),
                'second' => array('inStock' => 4, 'minPurchase' => 5),
                'third'  => array('inStock' => 3, 'minPurchase' => 2),
            ),
            array('first', 'second', 'third')
        );

        $this->assertEquals(2, $facet->getTotal());
    }


    private function search(
        FacetInterface $facet,
        $products,
        $expectedNumbers
    ) {
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        foreach($products as $number => $data) {
            $data = $this->getProduct($number, $context, $category, $data);
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
