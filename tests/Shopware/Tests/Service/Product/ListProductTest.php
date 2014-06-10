<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Struct\Context;
use Shopware\Tests\Service\Helper;

class ListProductTest extends \Enlight_Components_Test_TestCase
{
    private $helper;

    function __construct()
    {
        $this->helper = new Helper();
    }

    private function getListProduct($number, Context $context)
    {
        return Shopware()->Container()->get('list_product_service')
            ->get($number, $context);
    }

    public function testProductRequirements()
    {
        $number = 'List-Product-Test';

        $customerGroup = $this->helper->createCustomerGroup();
        $tax = $this->helper->createTax();

        $context = $this->helper->createContext(
            $customerGroup,
            $this->helper->getShop(),
            array($tax)
        );

        $data = $this->helper->getSimpleProduct(
            $number,
            $tax,
            $customerGroup
        );

        $data = array_merge($data, $this->helper->getConfigurator(
            $customerGroup
        ));

        $this->helper->createArticle($data);

        $product = $this->getListProduct($number, $context);


        $this->assertNotEmpty($product->getId());
        $this->assertNotEmpty($product->getVariantId());
        $this->assertNotEmpty($product->getName());
        $this->assertNotEmpty($product->getNumber());
        $this->assertNotEmpty($product->getManufacturer());
        $this->assertNotEmpty($product->getTax());
        $this->assertNotEmpty($product->getUnit());

        $this->assertInstanceOf('Shopware\Struct\ListProduct', $product);
        $this->assertInstanceOf('Shopware\Struct\Product\Unit', $product->getUnit());
        $this->assertInstanceOf('Shopware\Struct\Product\Manufacturer', $product->getManufacturer());

        $this->assertNotEmpty($product->getPrices());
        $this->assertNotEmpty($product->getPriceRules());
        foreach($product->getPrices() as $price) {
            $this->assertInstanceOf('Shopware\Struct\Product\Price', $price);
            $this->assertInstanceOf('Shopware\Struct\Product\Unit', $price->getUnit());
            $this->assertGreaterThanOrEqual(1, $price->getUnit()->getMinPurchase());
        }

        foreach($product->getPriceRules() as $price) {
            $this->assertInstanceOf('Shopware\Struct\Product\PriceRule', $price);
        }

        $this->assertInstanceOf('Shopware\Struct\Product\Price', $product->getCheapestPrice());
        $this->assertInstanceOf('Shopware\Struct\Product\PriceRule', $product->getCheapestPriceRule());
        $this->assertInstanceOf('Shopware\Struct\Product\Unit', $product->getCheapestPrice()->getUnit());
        $this->assertGreaterThanOrEqual(1, $product->getCheapestPrice()->getUnit()->getMinPurchase());

        $this->assertNotEmpty($product->getCheapestPriceRule()->getPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPseudoPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getFrom());

        $this->assertGreaterThanOrEqual(1, $product->getUnit()->getMinPurchase());
        $this->assertNotEmpty($product->getManufacturer()->getName());
    }
}