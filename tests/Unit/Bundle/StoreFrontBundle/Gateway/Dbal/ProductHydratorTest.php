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

namespace Shopware\Tests\Unit\Bundle\StoreFrontBundle\Gateway\Dbal;

use DateTimeInterface;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\EsdHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ManufacturerHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ProductHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\TaxHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\UnitHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

class ProductHydratorTest extends TestCase
{
    /**
     * @dataProvider assignProductDataTestDataProvider
     *
     * @param array<string,mixed> $data
     */
    public function testAssignProductData(array $data): void
    {
        $listProduct = new ListProduct(1, 1, '');
        $productHydrator = $this->getProductHydrator();
        $reflectionMethod = (new ReflectionClass(ProductHydrator::class))->getMethod('assignProductData');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs($productHydrator, [$listProduct, $data]);

        static::assertSame($data['__product_name'], $listProduct->getName(), '__product_name');
        static::assertSame($data['__product_description'], $listProduct->getShortDescription(), '__product_description');
        static::assertSame($data['__product_description_long'], $listProduct->getLongDescription(), '__product_description_long');
        static::assertSame($data['__product_metaTitle'], $listProduct->getMetaTitle(), '__product_metaTitle');
        static::assertSame($data['__product_keywords'], $listProduct->getKeywords(), '__product_keywords');
        static::assertSame($data['__product_template'], $listProduct->getTemplate(), '__product_template');
        static::assertSame($data['__product_has_esd'], $listProduct->hasEsd(), '__product_has_esd');
        static::assertSame($data['__product_pricegroupActive'], $listProduct->isPriceGroupActive(), '__product_pricegroupActive');
        static::assertSame($data['__topSeller_sales'], $listProduct->getSales(), '');
        static::assertSame($data['__variant_shippingfree'], $listProduct->isShippingFree(), '__topSeller_sales');
        static::assertSame($data['__variant_instock'], $listProduct->getStock(), '__variant_instock');
        static::assertSame($data['__product_main_detail_id'], $listProduct->getMainVariantId(), '__product_main_detail_id');
        static::assertSame($data['__variant_shippingtime'], $listProduct->getShippingTime(), '__variant_shippingtime');
        static::assertSame($data['__variant_releasedate'], $listProduct->getReleaseDate(), '__variant_releasedate');
        static::assertSame($data['__product_datum'], $this->getFormatedDateTime($listProduct->getCreatedAt()), '__product_datum');
        static::assertSame($data['__product_changetime'], $this->getFormatedDateTime($listProduct->getUpdatedAt()), '__product_changetime');
        static::assertSame($data['__variant_additionaltext'], $listProduct->getAdditional(), '__variant_additionaltext');
        static::assertSame($data['__variant_ean'], $listProduct->getEan(), '__variant_ean');
        static::assertSame($data['__variant_height'], $listProduct->getHeight(), '__variant_height');
        static::assertSame($data['__variant_length'], $listProduct->getLength(), '__variant_length');
        static::assertSame($data['__variant_stockmin'], $listProduct->getMinStock(), '__variant_stockmin');
        static::assertSame($data['__variant_weight'], $listProduct->getWeight(), '__variant_weight');
        static::assertSame($data['__variant_width'], $listProduct->getWidth(), '__variant_width');
        static::assertSame($data['__product_has_available_variants'], $listProduct->hasAvailableVariant(), '__product_has_available_variants');
        static::assertSame($data['__product_fallback_price_count'], $listProduct->getFallbackPriceCount(), '__product_fallback_price_count');

        static::assertSame($data['EXPECTED__product_blocked_customer_groups'], $listProduct->getBlockedCustomerGroupIds(), 'EXPECTED__product_blocked_customer_groups');
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function assignProductDataTestDataProvider(): Generator
    {
        $productData = require_once __DIR__ . '/_fixtures/ProductData.php';

        yield 'Blocked customer groups is empty string' => [
            $productData['blocked_customer_groups_is_empty_string'],
        ];

        yield 'Blocked customer groups is null' => [
            $productData['blocked_customer_groups_is_null'],
        ];

        yield 'With blocked customer groups' => [
            $productData['with_blocked_customer_groups'],
        ];
    }

    private function getFormatedDateTime(?DateTimeInterface $dateTime): ?string
    {
        if (!$dateTime instanceof DateTimeInterface) {
            return null;
        }

        return $dateTime->format('Y-m-d');
    }

    private function getProductHydrator(): ProductHydrator
    {
        $attributeHydrator = new AttributeHydrator();

        return new ProductHydrator(
            $attributeHydrator,
            new ManufacturerHydrator($attributeHydrator, $this->createMock(MediaServiceInterface::class)),
            new TaxHydrator(),
            new UnitHydrator(),
            new EsdHydrator($attributeHydrator)
        );
    }
}
