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

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Article\Unit as UnitModel;

class TranslationTest extends TestCase
{
    public function testListProductTranslation(): void
    {
        $number = 'Translation-Test';
        $context = $this->getContext();

        $product = $this->getProduct($number, $context);
        $createdProduct = $this->helper->createProduct($product);

        $this->helper->createArticleTranslation(
            $createdProduct->getId(),
            $context->getShop()->getId()
        );

        $listProduct = $this->helper->getListProduct(
            $number,
            $context
        );

        static::assertEquals('Dummy Translation', $listProduct->getName());
        static::assertEquals('Dummy Translation', $listProduct->getShortDescription());
        static::assertEquals('Dummy Translation', $listProduct->getLongDescription());
        static::assertEquals('Dummy Translation', $listProduct->getShippingTime());
        static::assertEquals('Dummy Translation', $listProduct->getAdditional());
        static::assertEquals('Dummy Translation', $listProduct->getKeywords());
        static::assertEquals('Dummy Translation', $listProduct->getMetaTitle());
        static::assertInstanceOf(Unit::class, $listProduct->getUnit());
        static::assertEquals('Dummy Translation', $listProduct->getUnit()->getPackUnit());
    }

    public function testManufacturerTranslation(): void
    {
        $number = 'Translation-Test';
        $context = $this->getContext();

        $product = $this->getProduct($number, $context);
        $createdProduct = $this->helper->createProduct($product);

        static::assertInstanceOf(Supplier::class, $createdProduct->getSupplier());
        $this->helper->createManufacturerTranslation(
            $createdProduct->getSupplier()->getId(),
            $context->getShop()->getId()
        );

        $product = $this->helper->getListProduct($number, $context);

        $manufacturer = $product->getManufacturer();
        static::assertInstanceOf(Manufacturer::class, $manufacturer);

        static::assertEquals('Dummy Translation', $manufacturer->getDescription());
        static::assertEquals('Dummy Translation', $manufacturer->getMetaTitle());
        static::assertEquals('Dummy Translation', $manufacturer->getMetaKeywords());
        static::assertEquals('Dummy Translation', $manufacturer->getMetaDescription());
    }

    public function testUnitTranslation(): void
    {
        $number = 'Unit-Translation';
        $context = $this->getContext();

        $product = $this->getProduct($number, $context);

        $product = array_merge(
            $product,
            $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                $number,
                ['Farbe' => ['rot', 'gelb']]
            )
        );

        $variant = $product['variants'][1];
        $variant['prices'] = $this->helper->getGraduatedPrices(
            $context->getCurrentCustomerGroup()->getKey(),
            -40
        );
        $variant['unit'] = ['name' => 'Test-Unit-Variant', 'unit' => 'ABC'];
        $product['variants'][1] = $variant;

        $createdProduct = $this->helper->createProduct($product);

        $unit = null;
        foreach ($createdProduct->getDetails() as $detail) {
            if ($variant['number'] === $detail->getNumber()) {
                $unit = $detail->getUnit();
                break;
            }
        }

        $data = [
            $unit->getId() => [
                'unit' => 'Dummy Translation 2',
                'description' => 'Dummy Translation 2',
            ],
        ];

        static::assertInstanceOf(Detail::class, $createdProduct->getMainDetail());
        static::assertInstanceOf(UnitModel::class, $createdProduct->getMainDetail()->getUnit());
        $this->helper->createUnitTranslations(
            [
                $createdProduct->getMainDetail()->getUnit()->getId(),
                $unit->getId(),
            ],
            $context->getShop()->getId(),
            $data
        );

        $listProduct = $this->helper->getListProduct(
            $number,
            $context
        );

        static::assertInstanceOf(Unit::class, $listProduct->getUnit());
        static::assertEquals('Dummy Translation', $listProduct->getUnit()->getUnit());
        static::assertEquals('Dummy Translation', $listProduct->getUnit()->getName());

        foreach ($listProduct->getPrices() as $price) {
            static::assertInstanceOf(Unit::class, $price->getUnit());
            static::assertEquals('Dummy Translation', $price->getUnit()->getUnit());
            static::assertEquals('Dummy Translation', $price->getUnit()->getName());
        }

        static::assertInstanceOf(Price::class, $listProduct->getCheapestPrice());
        static::assertInstanceOf(Unit::class, $listProduct->getCheapestPrice()->getUnit());
        static::assertEquals('Dummy Translation 2', $listProduct->getCheapestPrice()->getUnit()->getUnit());
        static::assertEquals('Dummy Translation 2', $listProduct->getCheapestPrice()->getUnit()->getName());
    }

    public function testPropertyTranslation(): void
    {
        $number = 'Property-Translation';
        $context = $this->getContext();

        $product = $this->getProduct($number, $context);
        $properties = $this->helper->getProperties(2, 2);
        $product = array_merge($product, $properties);

        $this->helper->createPropertyTranslation($properties['all'], $context->getShop()->getId());
        $this->helper->createProduct($product);

        $listProduct = $this->helper->getListProduct($number, $context);
        $property = $this->helper->getProductProperties($listProduct, $context);

        static::assertEquals('Dummy Translation', $property->getName());

        foreach ($property->getGroups() as $group) {
            $expected = 'Dummy Translation group - ' . $group->getId();
            static::assertEquals($expected, $group->getName());

            foreach ($group->getOptions() as $option) {
                $expected = 'Dummy Translation option - ' . $group->getId() . ' - ' . $option->getId();
                static::assertEquals($expected, $option->getName());
            }
        }
    }

    public function testConfiguratorTranslation(): void
    {
        $number = 'Configurator-Translation';
        $context = $this->getContext();

        $product = $this->getProduct($number, $context);

        $configurator = $this->helper->getConfigurator(
            $context->getCurrentCustomerGroup(),
            $number,
            [
                'Farbe' => ['rot', 'gelb'],
                'Größe' => ['L', 'M'],
            ]
        );

        $product = array_merge($product, $configurator);

        $this->helper->createConfiguratorTranslation(
            $configurator['configuratorSet'],
            $context->getShop()->getId()
        );

        $this->helper->createProduct($product);

        $listProduct = $this->helper->getListProduct($number, $context);

        $configurator = $this->helper->getProductConfigurator(
            $listProduct,
            $context
        );

        foreach ($configurator->getGroups() as $group) {
            $expected = 'Dummy Translation group - ' . $group->getId();
            static::assertEquals($expected, $group->getName());

            $expected = 'Dummy Translation description - ' . $group->getId();
            static::assertEquals($expected, $group->getDescription());

            foreach ($group->getOptions() as $option) {
                $expected = 'Dummy Translation option - ' . $group->getId() . ' - ' . $option->getId();
                static::assertEquals($expected, $option->getName());
            }
        }
    }

    protected function getContext(int $shopId = 1): TestContext
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $shop = $this->helper->getShop(2);

        return $this->helper->createContext(
            $customerGroup,
            $shop,
            [$tax]
        );
    }
}
