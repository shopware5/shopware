<?php

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;

class BasePriceTest extends TestCase
{
    public function testHigherReferenceUnit()
    {
        $number = 'Higher-Reference-Unit';
        $context = $this->getContext();

        $data = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $data['mainDetail'] = array_merge($data['mainDetail'], array(
            'purchaseUnit' => 0.5,
            'referenceUnit' => 1
        ));
        $data['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];

        $this->helper->createArticle($data);

        $product = $this->helper->getListProduct($number, $context);

        /** @var Price $first */
        $first = array_shift($product->getPrices());
        $this->assertEquals(100, $first->getCalculatedPrice());
        $this->assertEquals(200, $first->getCalculatedReferencePrice());

        /** @var Price $last */
        $last = array_pop($product->getPrices());
        $this->assertEquals(50, $last->getCalculatedPrice());
        $this->assertEquals(100, $last->getCalculatedReferencePrice());
    }

    public function testHigherPurchaseUnit()
    {
        $number = 'Higher-Purchase-Unit';
        $context = $this->getContext();

        $data = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $data['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];
        $data['mainDetail'] = array_merge($data['mainDetail'], array(
            'purchaseUnit' => 0.5,
            'referenceUnit' => 0.1
        ));

        $this->helper->createArticle($data);
        $product = $this->helper->getListProduct($number, $context);

        /**@var $first Price*/
        $first = array_shift($product->getPrices());
        $this->assertEquals(100, $first->getCalculatedPrice());
        $this->assertEquals(20, $first->getCalculatedReferencePrice());

        $last = array_pop($product->getPrices());
        $this->assertEquals(50, $last->getCalculatedPrice());
        $this->assertEquals(10, $last->getCalculatedReferencePrice());
    }
}
