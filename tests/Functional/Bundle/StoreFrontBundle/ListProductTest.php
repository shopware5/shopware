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

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class ListProductTest extends TestCase
{
    public function testProductRequirements(): void
    {
        $number = 'List-Product-Test';

        $context = $this->getContext();

        $data = $this->getProduct($number, $context);
        $data = array_merge(
            $data,
            $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                $number
            )
        );
        $this->helper->createProduct($data);

        $product = $this->getListProduct($number, $context);

        static::assertNotEmpty($product->getId());
        static::assertNotEmpty($product->getVariantId());
        static::assertNotEmpty($product->getName());
        static::assertNotEmpty($product->getNumber());
        static::assertNotEmpty($product->getManufacturer());
        static::assertNotEmpty($product->getTax());
        static::assertNotEmpty($product->getUnit());

        static::assertInstanceOf(Unit::class, $product->getUnit());
        static::assertInstanceOf(Manufacturer::class, $product->getManufacturer());

        static::assertNotEmpty($product->getPrices());
        static::assertNotEmpty($product->getPriceRules());
        foreach ($product->getPrices() as $price) {
            static::assertInstanceOf(Price::class, $price);
            static::assertInstanceOf(Unit::class, $price->getUnit());
            static::assertGreaterThanOrEqual(1, $price->getUnit()->getMinPurchase());
        }

        foreach ($product->getPriceRules() as $price) {
            static::assertInstanceOf(PriceRule::class, $price);
        }

        static::assertInstanceOf(Price::class, $product->getCheapestPrice());
        static::assertInstanceOf(PriceRule::class, $product->getCheapestPriceRule());
        static::assertInstanceOf(Unit::class, $product->getCheapestPrice()->getUnit());
        static::assertGreaterThanOrEqual(1, $product->getCheapestPrice()->getUnit()->getMinPurchase());

        static::assertNotEmpty($product->getCheapestPriceRule()->getPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getCalculatedPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getCalculatedPseudoPrice());
        static::assertNotEmpty($product->getCheapestPrice()->getFrom());
        static::assertNotEmpty($product->getCheapestPrice()->getCalculatedRegulationPrice());

        static::assertGreaterThanOrEqual(1, $product->getUnit()->getMinPurchase());
        static::assertNotEmpty($product->getManufacturer()->getName());
    }

    private function getListProduct(string $number, ShopContext $context): ListProduct
    {
        $product = Shopware()->Container()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($product);

        return $product;
    }
}
