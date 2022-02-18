<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Option;
use Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Set;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;

class ConfiguratorTest extends TestCase
{
    public function testVariantConfiguration(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $productData = $this->getProduct($number, $context);

        $this->helper->createProduct($productData);

        foreach ($productData['variants'] as $testVariant) {
            $product = Shopware()->Container()->get(ProductServiceInterface::class)
                ->get($testVariant['number'], $context);
            static::assertInstanceOf(Product::class, $product);

            static::assertCount(3, $product->getConfiguration());

            $optionNames = array_column($testVariant['configuratorOptions'], 'option');

            foreach ($product->getConfiguration() as $configuratorGroup) {
                static::assertCount(1, $configuratorGroup->getOptions());
                $options = $configuratorGroup->getOptions();
                $option = array_shift($options);
                static::assertInstanceOf(Option::class, $option);
                static::assertContains($option->getName(), $optionNames);
            }
        }
    }

    public function testDefaultConfigurator(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);

        $product = Shopware()->Container()->get(ListProductServiceInterface::class)
            ->get($number, $context);
        static::assertInstanceOf(ListProduct::class, $product);

        $configurator = Shopware()->Container()->get(ConfiguratorServiceInterface::class)
            ->getProductConfigurator($product, $context, []);

        static::assertInstanceOf(Set::class, $configurator);

        static::assertCount(3, $configurator->getGroups());
        foreach ($configurator->getGroups() as $group) {
            static::assertCount(3, $group->getOptions());
            static::assertContains($group->getName(), ['Farbe', 'Größe', 'Form']);

            foreach ($group->getOptions() as $option) {
                switch ($group->getName()) {
                    case 'Farbe':
                        static::assertContains($option->getName(), ['rot', 'blau', 'grün']);
                        break;
                    case 'Größe':
                        static::assertContains($option->getName(), ['L', 'M', 'S']);
                        break;
                    case 'Form':
                        static::assertContains($option->getName(), ['rund', 'eckig', 'oval']);
                        break;
                }
            }
        }
    }

    public function testSelection(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $this->helper->createProduct($data);

        $product = Shopware()->Container()->get(ListProductServiceInterface::class)
            ->get($number, $context);
        static::assertInstanceOf(ListProduct::class, $product);

        $selection = $this->createSelection($product, [
            'rot', 'L',
        ]);

        $configurator = Shopware()->Container()->get(ConfiguratorServiceInterface::class)
            ->getProductConfigurator($product, $context, $selection);

        foreach ($configurator->getGroups() as $group) {
            switch ($group->getName()) {
                case 'Farbe':
                case 'Größe':
                    static::assertTrue($group->isSelected());
                    break;
                case 'Form':
                    static::assertFalse($group->isSelected());
                    break;
            }

            foreach ($group->getOptions() as $option) {
                static::assertTrue($option->getActive());

                switch ($option->getName()) {
                    case 'rot':
                    case 'L':
                        static::assertTrue($option->isSelected());
                        break;
                    default:
                        static::assertFalse($option->isSelected());
                        break;
                }
            }
        }
    }

    public function testSelectionConfigurator(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);

        $createdProduct = $this->helper->createProduct($data);

        $this->helper->updateConfiguratorVariants(
            $createdProduct->getId(),
            [
                [
                    'options' => ['rot', 'L'],
                    'data' => ['active' => false],
                ],
                [
                    'options' => ['blau', 'S'],
                    'data' => ['active' => false],
                ],
                [
                    'options' => ['rund', 'M'],
                    'data' => ['active' => false],
                ],
            ]
        );

        $product = Shopware()->Container()->get(ListProductServiceInterface::class)
            ->get($number, $context);
        static::assertInstanceOf(ListProduct::class, $product);

        $selection = $this->createSelection($product, ['rot']);
        $configurator = Shopware()->Container()->get(ConfiguratorServiceInterface::class)
            ->getProductConfigurator($product, $context, $selection);
        $this->assertInactiveOptions($configurator, ['L']);

        $selection = $this->createSelection($product, ['L']);
        $configurator = Shopware()->Container()->get(ConfiguratorServiceInterface::class)
            ->getProductConfigurator($product, $context, $selection);
        $this->assertInactiveOptions($configurator, ['rot']);

        $selection = $this->createSelection($product, ['blau', 'rund']);
        $configurator = Shopware()->Container()->get(ConfiguratorServiceInterface::class)
            ->getProductConfigurator($product, $context, $selection);

        $this->assertInactiveOptions($configurator, ['M', 'S']);
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        Category $category = null,
        $additionally = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            [
                'Farbe' => ['rot', 'blau', 'grün'],
                'Größe' => ['L', 'M', 'S'],
                'Form' => ['rund', 'eckig', 'oval'],
            ]
        );

        return array_merge($product, $configurator);
    }

    /**
     * @param array<string> $optionNames
     *
     * @return array<int, int>
     */
    private function createSelection(ListProduct $listProduct, array $optionNames): array
    {
        $options = $this->helper->getProductOptionsByName(
            $listProduct->getId(),
            $optionNames
        );

        $selection = [];
        foreach ($options as $option) {
            $groupId = (int) $option['group_id'];
            $selection[$groupId] = (int) $option['id'];
        }

        return $selection;
    }

    /**
     * @param array<string> $expectedOptions
     */
    private function assertInactiveOptions(Set $configurator, array $expectedOptions): void
    {
        foreach ($configurator->getGroups() as $group) {
            foreach ($group->getOptions() as $option) {
                if (\in_array($option->getName(), $expectedOptions, true)) {
                    static::assertFalse($option->getActive());
                } else {
                    static::assertTrue($option->getActive());
                }
            }
        }
    }
}
