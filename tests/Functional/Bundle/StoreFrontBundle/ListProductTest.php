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
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Manufacturer;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Price;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;

class ListProductTest extends TestCase
{
    const INACTIVE_PRODUCTNUMBER = 'SW10239';

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

        $this->assertInstanceOf(ListProduct::class, $product);
        $this->assertInstanceOf(Unit::class, $product->getUnit());
        $this->assertInstanceOf(Manufacturer::class, $product->getManufacturer());

        $this->assertNotEmpty($product->getPrices());
        $this->assertNotEmpty($product->getPriceRules());
        foreach ($product->getPrices() as $price) {
            $this->assertInstanceOf(Price::class, $price);
            $this->assertInstanceOf(Unit::class, $price->getUnit());
            $this->assertGreaterThanOrEqual(1, $price->getUnit()->getMinPurchase());
        }

        foreach ($product->getPriceRules() as $price) {
            $this->assertInstanceOf(PriceRule::class, $price);
        }

        $this->assertInstanceOf(Price::class, $product->getCheapestPrice());
        $this->assertInstanceOf(PriceRule::class, $product->getCheapestPriceRule());
        $this->assertInstanceOf(Unit::class, $product->getCheapestPrice()->getUnit());
        $this->assertGreaterThanOrEqual(1, $product->getCheapestPrice()->getUnit()->getMinPurchase());

        $this->assertNotEmpty($product->getCheapestPriceRule()->getPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getCalculatedPseudoPrice());
        $this->assertNotEmpty($product->getCheapestPrice()->getFrom());

        $this->assertGreaterThanOrEqual(1, $product->getUnit()->getMinPurchase());
        $this->assertNotEmpty($product->getManufacturer()->getName());
    }

    public function testInactiveArticleWithoutAdminMode()
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $this->assertNull($this->getListProduct(self::INACTIVE_PRODUCTNUMBER, $context));
    }

    public function testInactiveArticleWithAdminMode()
    {
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $context->setAdmin(true);
        $this->assertInstanceOf(ListProduct::class, $this->getListProduct(self::INACTIVE_PRODUCTNUMBER, $context));
        $context->setAdmin(false);
    }

    /**
     * @param $number
     * @param ShopContext $context
     *
     * @return ListProduct
     */
    private function getListProduct($number, ShopContext $context)
    {
        return Shopware()->Container()->get('shopware_storefront.list_product_service')
            ->get($number, $context);
    }
}
