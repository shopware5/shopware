<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Service\TestCase;

class ConfiguratorTest extends TestCase
{
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ) {
        $product = parent::getProduct($number, $context, $category);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            array(
                'Farbe' => array('rot', 'blau', 'grün'),
                'Größe' => array('L', 'M', 'S'),
                'Form' => array('rund', 'eckig', 'oval')
            )
        );

        $product = array_merge($product, $configurator);
        return $product;
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

    public function testVariantConfiguration()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $productData = $this->getProduct($number, $context);

        $this->helper->createArticle($productData);

        foreach ($productData['variants'] as $testVariant) {
            $product = Shopware()->Container()->get('shopware_storefront.product_service')
                ->get($testVariant['number'], $context);

            $this->assertCount(3, $product->getConfiguration());

            $optionNames = array_column($testVariant['configuratorOptions'], 'option');

            foreach ($product->getConfiguration() as $configuratorGroup) {
                $this->assertCount(1, $configuratorGroup->getOptions());
                $option = array_shift($configuratorGroup->getOptions());
                $this->assertContains($option->getName(), $optionNames);
            }
        }
    }

    public function testDefaultConfigurator()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $product = Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->get($number, $context);

        $configurator = Shopware()->Container()->get('shopware_storefront.configurator_service')
            ->getProductConfigurator($product, $context, array());

        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set', $configurator);

        $this->assertCount(3, $configurator->getGroups());
        foreach ($configurator->getGroups() as $group) {
            $this->assertCount(3, $group->getOptions());
            $this->assertContains($group->getName(), array('Farbe', 'Größe', 'Form'));

            foreach ($group->getOptions() as $option) {
                switch ($group->getName()) {
                    case "Farbe":
                        $this->assertContains($option->getName(), array('rot', 'blau', 'grün'));
                        break;
                    case "Größe":
                        $this->assertContains($option->getName(), array('L', 'M', 'S'));
                        break;
                    case "Form":
                        $this->assertContains($option->getName(), array('rund', 'eckig', 'oval'));
                        break;
                }
            }
        }
    }

    public function testSelection()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createArticle($data);

        $product = Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->get($number, $context);

        $selection = $this->createSelection($product, array(
            'rot', 'L'
        ));

        $configurator = Shopware()->Container()->get('shopware_storefront.configurator_service')
            ->getProductConfigurator($product, $context, $selection);

        foreach ($configurator->getGroups() as $group) {
            switch ($group->getName()) {
                case "Farbe":
                    $this->assertTrue($group->isSelected());
                    break;
                case "Größe":
                    $this->assertTrue($group->isSelected());
                    break;
                case "Form":
                    $this->assertFalse($group->isSelected());
                    break;
            }

            foreach ($group->getOptions() as $option) {
                $this->assertTrue($option->getActive());

                switch ($option->getName()) {
                    case "rot":
                    case "L":
                        $this->assertTrue($option->isSelected());
                        break;
                    default:
                        $this->assertFalse($option->isSelected());
                        break;
                }
            }
        }
    }

    public function testSelectionConfigurator()
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $article = $this->helper->createArticle($data);

        $this->helper->updateConfiguratorVariants(
            $article->getId(),
            array(
                array(
                    'options' => array('rot', 'L'),
                    'data' => array('active' => false)
                ),
                array(
                    'options' => array('blau', 'S'),
                    'data' => array('active' => false)
                ),
                array(
                    'options' => array('rund', 'M'),
                    'data' => array('active' => false)
                )
            )
        );

        $product = Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->get($number, $context);

        $selection = $this->createSelection($product, array('rot'));
        $configurator = Shopware()->Container()->get('shopware_storefront.configurator_service')
            ->getProductConfigurator($product, $context, $selection);
        $this->assertInactiveOptions($configurator, array('L'));

        $selection = $this->createSelection($product, array('L'));
        $configurator = Shopware()->Container()->get('shopware_storefront.configurator_service')
            ->getProductConfigurator($product, $context, $selection);
        $this->assertInactiveOptions($configurator, array('rot'));

        $selection = $this->createSelection($product, array('blau', 'rund'));
        $configurator = Shopware()->Container()->get('shopware_storefront.configurator_service')
            ->getProductConfigurator($product, $context, $selection);

        $this->assertInactiveOptions($configurator, array('M', 'S'));
    }

    private function assertInactiveOptions(Set $configurator, $expectedOptions)
    {
        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                if (in_array($option->getName(), $expectedOptions)) {
                    $this->assertFalse($option->getActive());
                } else {
                    $this->assertTrue($option->getActive());
                }
            }
        }
    }
}
