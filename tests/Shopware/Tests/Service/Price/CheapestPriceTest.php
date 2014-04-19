<?php

/**
 * Class Shopware_Tests_Service_Price_CheapestPriceTest
 */
class Shopware_Tests_Service_Price_CheapestPriceTest extends Shopware_Tests_Service_Base
{
    public function testVariantPrices()
    {
        $number = 'VariantPrice';
        $group = $this->createCustomerGroup('TEST', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertInstanceOf('Shopware\Struct\Product\Price', $price);
        $this->assertEquals(1000, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testDiscount()
    {
        $number = 'Discount';
        $group = $this->createCustomerGroup('TEST', 10);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());

        $this->createArticle($data);
        $tax = $this->getHighTax();

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $tax
        );

        $product = $this->getProduct($number, $state);
        $price = $product->getCheapestPrice();

        $this->assertInstanceOf('Shopware\Struct\Product\Price', $price);
        $this->assertEquals(900, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testPriceGroup()
    {
        $number = 'PriceGroup';
        $group = $this->createCustomerGroup('TEST', 0);

        $priceGroup = $this->createPriceGroup(array(
            array(
                'customerGroup' => 'TEST',
                'quantity' => 1,
                'discount' => 10
            )
        ));

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['priceGroupActive'] = true;
        $data['priceGroupId'] = $priceGroup->getId();

        $this->createArticle($data);

        $tax = $this->getHighTax();

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $tax
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertEquals(900, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testPriceGroupAndDiscount()
    {
        $number = 'PriceGroupAndDiscount';
        $group = $this->createCustomerGroup('TEST', 10);

        $priceGroup = $this->createPriceGroup(array(
            array(
                'customerGroup' => 'TEST',
                'quantity' => 1,
                'discount' => 10
            )
        ));

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['priceGroupActive'] = true;
        $data['priceGroupId'] = $priceGroup->getId();

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertEquals(810, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testSimpleConfigurator() {

        $number = 'Configurator-1';
        $group = 'TEST';

        $this->removeArticle($number);
        $group = $this->createCustomerGroup($group, 0);

        $configurator = $this->getSimpleConfigurator(1, 3);
        $variants = $this->generateVariants(
            $configurator['groups'],
            array(
                'prices' => $this->getScaledPrices($group->getKey(), -600)
            )
        );

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['configuratorSet'] = $configurator;
        $data['variants'] = $variants;

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertEquals(400, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testMinPurchase()
    {
        $number = 'PurchasePrice';
        $group = $this->createCustomerGroup('TEST', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices(
            $group->getKey()
        );
        $data['mainDetail']['minPurchase'] = 2;

        $this->createArticle($data);
        $tax = $this->getHighTax();

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $tax
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertEquals(2000, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }

    public function testAllCombinations()
    {
        $number = 'Combination-1';
        $group = 'TEST';

        $this->removeArticle($number);

        $group = $this->createCustomerGroup($group, 10);

        $priceGroup = $this->createPriceGroup(array(
            array(
                'customerGroup' => $group->getKey(),
                'quantity' => 1,
                'discount' => 10
            ),
            array(
                'customerGroup' => $group->getKey(),
                'quantity' => 4,
                'discount' => 20
            )
        ));

        $configurator = $this->getSimpleConfigurator(1, 3);
        $variants = $this->generateVariants(
            $configurator['groups'],
            array('prices' => $this->getScaledPrices($group->getKey(), -400))
        );

        foreach($variants as &$variant) {
            $variant['minpurchase'] = 4;
        }

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['priceGroupActive'] = true;
        $data['priceGroupId'] = $priceGroup->getId();
        $data['mainDetail']['minpurchase'] = 4;

        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['configuratorSet'] = $configurator;
        $data['variants'] = $variants;

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $price = $product->getCheapestPrice();

        $this->assertEquals(1728, $price->getCalculatedPrice());
        $this->removeArticle($number);
    }
}