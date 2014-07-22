<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Tests\Service\Helper;

class ConfiguratorTest extends \Enlight_Components_Test_TestCase
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
     * @param $context
     * @return array
     */
    private function getProduct($number, Context $context)
    {
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($context->getTaxRules()),
            $context->getCurrentCustomerGroup()
        );

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            array(
                'Farbe' => array('rot', 'blau', 'grün'),
                'Größe' => array('L', 'M', 'S'),
                'Sekundär' => array('schwarz', 'weiß', 'grau')
            )
        );

        $product = array_merge($product, $configurator);
        return $product;
    }


    public function testLegacyFioo()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $configurator = $this->helper->getProductConfigurator($listProduct, $context);

        $this->assertCount(3, $configurator->getGroups());
        foreach ($configurator->getGroups() as $group) {
            $this->assertCount(3, $group->getOptions());
        }
    }

    public function testProductConfigurator()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $configurator = $this->helper->getProductConfigurator($listProduct, $context);

        $this->assertCount(3, $configurator->getGroups());
        foreach ($configurator->getGroups() as $group) {
            $this->assertCount(3, $group->getOptions());
        }
    }

    private function createSelection(ListProduct $listProduct, array $optionNames)
    {
        $options = $this->helper->getProductOptionsByName(
            $listProduct->getId(),
            $optionNames
        );

        $selection = array();
        foreach ($options as $option) {
            $groupId = $option['group_id'];
            $selection[$groupId] = $option['id'];
        }

        return $selection;
    }

    /**
     * @group knownFailing
     */
    public function testSelection()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $this->helper->updateConfiguratorVariants(
            $listProduct->getId(),
            array(
                array(
                    'options' => array('rot', 'schwarz'),
                    'data' => array('active' => false)
                ),
                array(
                    'options' => array('L'),
                    'data' => array('active' => false)
                ),
            )
        );

        $configurator = $this->helper->getProductConfigurator(
            $listProduct,
            $context,
            $this->createSelection($listProduct, array('rot'))
        );

        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                $this->assertNotEquals('schwarz', $option->getName());
                $this->assertNotEquals('L', $option->getName());
            }
        }


        $configurator = $this->helper->getProductConfigurator(
            $listProduct,
            $context,
            $this->createSelection($listProduct, array('schwarz'))
        );

        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                $this->assertNotEquals('rot', $option->getName());
                $this->assertNotEquals('L', $option->getName());
            }
        }
    }

    /**
     * @group knownFailing
     */
    public function testCloseoutSelection()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $listProduct = $this->helper->getListProduct($number, $context);

        $this->helper->updateConfiguratorVariants(
            $listProduct->getId(),
            array(
                array(
                    'options' => array('blau', 'weiß'),
                    'data' => array('inStock' => 0)
                ),
                array(
                    'options' => array('M'),
                    'data' => array('inStock' => 0)
                ),
            )
        );

        $configurator = $this->helper->getProductConfigurator(
            $listProduct,
            $context,
            $this->createSelection($listProduct, array('weiß'))
        );

        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                $this->assertNotEquals('blau', $option->getName());
                $this->assertNotEquals('M', $option->getName());
            }
        }

        $configurator = $this->helper->getProductConfigurator(
            $listProduct,
            $context,
            $this->createSelection($listProduct, array('blau'))
        );

        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                $this->assertNotEquals('weiß', $option->getName());
                $this->assertNotEquals('M', $option->getName());
            }
        }
    }
}
