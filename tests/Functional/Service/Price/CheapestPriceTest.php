<?php

namespace Shopware\Tests\Service\Price;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Tests\Service\TestCase;

class CheapestPriceTest extends TestCase
{
    private function getConfiguratorProduct($number, ShopContextInterface $context) // ShopContext
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            array('farbe' => array('rot', 'blau', 'grün', 'schwarz', 'weiß'))
        );
        $product = array_merge($product, $configurator);
        $product['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];

        foreach ($product['variants'] as $index => &$variant) {
            $offset = ($index + 1) * -10;

            $variant['prices'] = $this->helper->getGraduatedPrices(
                $context->getCurrentCustomerGroup()->getKey(),
                $offset
            );
        }

        return $product;
    }

    public function testCheapestPriceWithVariants()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        $cheapestPrice = $listProduct->getCheapestPrice();
        $this->assertEquals(50, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(60, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(100, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithInactiveVariants()
    {
        $number = 'testCheapestWithInactiveVariants';
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $count = count($data['variants']) - 1;

        $data['variants'][$count]['active'] = false;
        $data['variants'][$count - 1]['active'] = false;

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();

        $this->assertEquals(70, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(80, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(140, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithCloseout()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $count = count($data['variants']) - 1;

        $data['lastStock'] = true;
        $data['variants'][$count]['inStock'] = 0;
        $data['variants'][$count - 1]['inStock'] = 0;
        $data['variants'][$count - 2]['inStock'] = 0;

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();
        $this->assertEquals(80, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(90, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(160, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithMinPurchase()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $last = array_pop($data['variants']);
        $last['prices'] = array(array(
            'from' => 1,
            'to' => null,
            'price' => 5,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
            'pseudoPrice' => 6
        ));
        $last['minPurchase'] = 3;
        $data['variants'][] = $last;

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();

        /**
         * Expect price * minPurchase calculation
         */
        $this->assertEquals(15, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(18, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(10, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithMinPurchaseAndCloseout()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $last = array_pop($data['variants']);
        $last['prices'] = array(array(
            'from' => 1,
            'to' => null,
            'price' => 5,
            'customerGroupKey' => $context->getCurrentCustomerGroup()->getKey(),
            'pseudoPrice' => 6
        ));

        /**
         * Variant isn't active because minPurchase > inStock
         */
        $last['minPurchase'] = 3;
        $last['inStock'] = 2;
        $data['variants'][] = $last;

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        $cheapestPrice = $listProduct->getCheapestPrice();
        $this->assertEquals(60, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(70, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(120, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestForCustomerGroup()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);
        $this->helper->createCustomerGroup(array('key' => 'FORCE'));

        /**
         * Creates a 1€ price for the customer group FORCE
         * This is the "whole" cheapest price for all customer groups
         */
        foreach ($data['variants'] as &$variant) {
            $variant['prices'][] = array(
                'from' => 1,
                'to' => null,
                'price' => 1,
                'customerGroupKey' => 'FORCE',
                'pseudoPrice' => 2
            );
        }

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();

        /**
         * Expect that the cheapest price calculation works
         * correctly with only the fallback and current customer group
         *
         * PHP Cheapest price = 50,-€  (current customer group)
         * FORCE price = 1,-€
         *
         */
        $this->assertEquals('PHP', $cheapestPrice->getCustomerGroup()->getKey());
        $this->assertEquals(50, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(60, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(100, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithFallback()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        /**
         * Switch customer group key, this customer group has
         * no defined product prices.
         */
        $customerGroup = $context->getCurrentCustomerGroup();
        $customerGroup->setKey('FORCE-FALLBACK');

        $this->helper->createArticle($data);
        $listProduct = $this->helper->getListProduct($number, $context);

        $cheapestPrice = $listProduct->getCheapestPrice();

        /**
         * Expect that no FORCE-FALLBACK customer group prices found.
         */
        $this->assertEquals('PHP', $cheapestPrice->getCustomerGroup()->getKey());
        $this->assertEquals(50, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(60, $cheapestPrice->getCalculatedPseudoPrice());
        $this->assertEquals(100, $cheapestPrice->getCalculatedReferencePrice());
    }

    public function testCheapestWithPriceGroup()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $priceGroup = $this->helper->createPriceGroup();
        $priceGroupStruct = $this->converter->convertPriceGroup($priceGroup);
        $context->setPriceGroups(array(
            $priceGroupStruct->getId() => $priceGroupStruct
        ));

        $data['priceGroupActive'] = true;
        $data['priceGroupId'] = $priceGroup->getId();

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();

        /**
         * Expect cheapest variant 50€
         * And price group discount 10%
         */
        $this->assertEquals(45, $cheapestPrice->getCalculatedPrice());
    }

    public function testCheapestWithPriceGroupAndCloseout()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getConfiguratorProduct($number, $context);

        $priceGroup = $this->helper->createPriceGroup();
        $priceGroupStruct = $this->converter->convertPriceGroup($priceGroup);
        $context->setPriceGroups(array(
            $priceGroupStruct->getId() => $priceGroupStruct
        ));

        $data['priceGroupActive'] = true;
        $data['priceGroupId'] = $priceGroup->getId();


        $count = count($data['variants']) - 1;
        $data['lastStock'] = true;

        /**
         * Cheapest variant is now 80,- €
         */
        $data['variants'][$count]['inStock'] = 0;
        $data['variants'][$count - 1]['inStock'] = 0;
        $data['variants'][$count - 2]['inStock'] = 0;

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);
        $cheapestPrice = $listProduct->getCheapestPrice();

        /**
         * Expect cheapest variant 80,- €
         * And price group discount 10%
         */
        $this->assertEquals(72, $cheapestPrice->getCalculatedPrice());
        $this->assertEquals(144, $cheapestPrice->getCalculatedReferencePrice());
    }
}
