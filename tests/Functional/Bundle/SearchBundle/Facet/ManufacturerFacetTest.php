<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\ManufacturerFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ManufacturerFacetTest extends TestCase
{
    public function testWithNoManufacturer(): void
    {
        $result = $this->search(
            [
                'first' => null,
                'second' => null,
            ],
            ['first', 'second'],
            null,
            [],
            [new ManufacturerFacet()]
        );

        foreach ($result->getFacets() as $facet) {
            static::assertNotInstanceOf(ManufacturerFacet::class, $facet);
        }
    }

    public function testSingleManufacturer(): void
    {
        $supplier = $this->helper->createManufacturer();

        $result = $this->search(
            [
                'first' => $supplier,
                'second' => $supplier,
                'third' => null,
            ],
            ['first', 'second', 'third'],
            null,
            [],
            [new ManufacturerFacet()]
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(ValueListFacetResult::class, $facet);

        static::assertCount(1, $facet->getValues());
        static::assertEquals($supplier->getId(), $facet->getValues()[0]->getId());
    }

    public function testMultipleManufacturers(): void
    {
        $supplier1 = $this->helper->createManufacturer();
        $supplier2 = $this->helper->createManufacturer([
            'name' => 'Test-Manufacturer-2',
        ]);

        $result = $this->search(
            [
                'first' => $supplier1,
                'second' => $supplier1,
                'third' => $supplier2,
                'fourth' => null,
            ],
            ['first', 'second', 'third', 'fourth'],
            null,
            [],
            [new ManufacturerFacet()]
        );

        $facet = $result->getFacets()[0];
        static::assertInstanceOf(ValueListFacetResult::class, $facet);
        static::assertCount(2, $facet->getValues());
    }

    /**
     * @param string        $number
     * @param Supplier|null $manufacturer
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        ?Category $category = null,
        $manufacturer = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        if ($manufacturer) {
            $product['supplierId'] = $manufacturer->getId();
        } else {
            $product['supplierId'] = null;
        }

        return $product;
    }
}
