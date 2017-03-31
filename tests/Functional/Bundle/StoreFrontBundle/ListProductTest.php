<?php
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

use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class ListProductTest extends TestCase
{
    public function testProductRequirements()
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
        $this->helper->createArticle($data);

        $product = $this->getListProduct($number, $context);

        $this->assertNotEmpty($product->getId());
        $this->assertNotEmpty($product->getVariantId());
        $this->assertNotEmpty($product->getName());
        $this->assertNotEmpty($product->getNumber());
        $this->assertNotEmpty($product->getManufacturer());
        $this->assertNotEmpty($product->getTax());
        $this->assertNotEmpty($product->getUnit());

        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\ListProduct', $product);
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $product->getUnit());
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer', $product->getManufacturer());

        $this->assertNotEmpty($product->getPrices());
        $this->assertNotEmpty($product->getPriceRules());
        foreach ($product->getPrices() as $price) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Price', $price);
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $price->getUnit());
            $this->assertGreaterThanOrEqual(1, $price->getUnit()->getMinPurchase());
        }

        foreach ($product->getPriceRules() as $price) {
            $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule', $price);
        }

        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Price', $product->getCheapestPrice());
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule', $product->getCheapestPriceRule());
        $this->assertInstanceOf('Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit', $product->getCheapestPrice()->getUnit());
        $this->assertGreaterThanOrEqual(1, $product->getCheapestPrice()->getUnit()->getMinPurchase());

        $this->assertNotEmpty($product->getCheapestPriceRule()->getPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPseudoPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getFrom());

        $this->assertGreaterThanOrEqual(1, $product->getUnit()->getMinPurchase());
        $this->assertNotEmpty($product->getManufacturer()->getName());
    }

    /**
     * @param $number
     * @param ShopContext $context
     *
     * @return ListProduct
     */
    private function getListProduct($number, ShopContext $context)
    {
        return 🦄()->Container()->get('shopware_storefront.list_product_service')
            ->get($number, $context);
    }
}
