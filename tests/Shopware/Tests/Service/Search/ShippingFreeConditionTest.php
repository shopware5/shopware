<?php

namespace Shopware\Tests\Service\Search;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\Helper;

class ShippingFreeConditionTest extends TestCase
{
    /**
     * @var Helper
     */
    private $helper;

    protected function setUp()
    {
        $this->helper = new Helper();
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }

    /**
     * @return Context
     */
    private function getContext()
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $shop = $this->helper->getShop();

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            array($tax)
        );
    }

    /**
     * @param $number
     * @param \Shopware\Models\Category\Category $category
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @param bool $shippingFree
     * @return array
     */
    private function getDefaultProduct(
        $number,
        Category $category,
        Context $context,
        $shippingFree = true
    ) {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $product['categories'] = array(
            array('id' => $category->getId())
        );

        $product['mainDetail']['shippingFree'] = $shippingFree;

        return $product;
    }

    public function testShippingFree()
    {
        $category = $this->helper->createCategory();
        $context = $this->getContext();

        $articles = array(
            $this->getDefaultProduct('testShippingFree-1', $category, $context),
            $this->getDefaultProduct('testShippingFree-2', $category, $context, false),
        );

        foreach ($articles as $article) {
            $this->helper->createArticle($article);
        }

        $criteria = new Criteria();
        $criteria->addCategoryCondition(array($category->getId()));
        $criteria->addShippingFreeCondition();

        /**@var $result ProductNumberSearchResult*/
        $result = Shopware()->Container()->get('product_number_search_dbal')->search($criteria, $context);

        $this->assertSearchResult(
            $result,
            array('testShippingFree-1')
        );
    }

}
