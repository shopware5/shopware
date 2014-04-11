<?php

/**
 * Class Shopware_Tests_Service_Price_BasePriceTest
 */
class Shopware_Tests_Service_Price_BasePriceTest extends Shopware_Tests_Service_Base
{
    public function testSimpleVariant()
    {
        $number = 'VariantBasePrice';
        $group = $this->createCustomerGroup('TE', 0);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['mainDetail'] += $this->getUnitData();

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $prices = $product->getPrices();

        $this->assertEquals(200, $prices[0]->getCalculatedReferencePrice());
        $this->assertEquals(150, $prices[1]->getCalculatedReferencePrice());
        $this->assertEquals(100, $prices[2]->getCalculatedReferencePrice());

        foreach($prices as $price) {
            $this->assertEquals(100, $price->getUnit()->getReferenceUnit());
            $this->assertEquals(500, $price->getUnit()->getPurchaseUnit());
        }

        $cheapest = $product->getCheapestPrice();
        $this->assertEquals(200, $cheapest->getCalculatedReferencePrice());

        $this->assertEquals(100, $cheapest->getUnit()->getReferenceUnit());
        $this->assertEquals(500, $cheapest->getUnit()->getPurchaseUnit());
        $this->removeArticle($number);
    }


    public function testVariantAcross()
    {
        $number = 'Configurator';
        $group = 'TE';

        $this->removeArticle($number);
        $group = $this->createCustomerGroup($group, 0);

        $configurator = $this->getSimpleConfigurator(1, 3);

        $variantData = array('prices' => $this->getScaledPrices($group->getKey(), -400));
        $variantData += $this->getUnitData();

        $variants = $this->generateVariants(
            $configurator['groups'],
            $variantData
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

        $this->assertEquals(600, $price->getCalculatedPrice());
        $this->assertEquals(120, $price->getCalculatedReferencePrice());

        $this->removeArticle($number);
    }


    public function testBasePriceWithDiscount() {
        $number = 'VariantBasePrice';
        $group = $this->createCustomerGroup('TE', 20);

        $data = $this->getBaseData();
        $data['mainDetail'] = $this->getSimpleDetail($number);
        $data['mainDetail']['prices'] = $this->getScaledPrices($group->getKey());
        $data['mainDetail'] += $this->getUnitData();

        $this->createArticle($data);

        $state = $this->createGlobalState(
            $group,
            $this->getShop(),
            $this->getHighTax()
        );

        $product = $this->getProduct($number, $state);

        $prices = $product->getPrices();


        $this->assertEquals(160, $prices[0]->getCalculatedReferencePrice());
        $this->assertEquals(120, $prices[1]->getCalculatedReferencePrice());
        $this->assertEquals(80, $prices[2]->getCalculatedReferencePrice());

        foreach($prices as $price) {
            $this->assertEquals(100, $price->getUnit()->getReferenceUnit());
            $this->assertEquals(500, $price->getUnit()->getPurchaseUnit());
        }

        $cheapest = $product->getCheapestPrice();
        $this->assertEquals(160, $cheapest->getCalculatedReferencePrice());

        $this->assertEquals(100, $cheapest->getUnit()->getReferenceUnit());
        $this->assertEquals(500, $cheapest->getUnit()->getPurchaseUnit());
        $this->removeArticle($number);
    }


    public function testDifferentUnits() {
        $number = 'Configurator';
        $group = 'TE';

        $this->removeArticle($number);
        $group = $this->createCustomerGroup($group, 0);

        $configurator = $this->getSimpleConfigurator(1, 3);

        $variantData = array('prices' => $this->getScaledPrices($group->getKey(), -200));
        $variantData += $this->getUnitData(1600, 200);

        $variants = $this->generateVariants(
            $configurator['groups'],
            $variantData
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
        $this->assertEquals(800, $price->getCalculatedPrice());
        $this->assertEquals(100, $price->getCalculatedReferencePrice());

        $this->assertEquals(200, $price->getUnit()->getReferenceUnit());
        $this->assertEquals(1600, $price->getUnit()->getPurchaseUnit());
        $this->removeArticle($number);
    }
}