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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Facet;

use Shopware\Bundle\SearchBundle\Facet\ShippingFreeFacet;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ShippingFreeFacetTest extends TestCase
{
    public function testShippingFree(): void
    {
        $facet = new ShippingFreeFacet();
        $result = $this->search(
            [
                'first' => true,
                'second' => false,
                'third' => true,
            ],
            ['first', 'second', 'third'],
            null,
            [],
            [$facet]
        );

        static::assertCount(1, $result->getFacets());
        static::assertInstanceOf(BooleanFacetResult::class, $result->getFacets()[0]);
    }

    public function testShippingFreeWithoutMatch(): void
    {
        $facet = new ShippingFreeFacet();
        $result = $this->search(
            [
                'first' => false,
                'second' => false,
                'third' => false,
            ],
            ['first', 'second', 'third'],
            null,
            [],
            [$facet]
        );

        static::assertCount(0, $result->getFacets());
    }

    /**
     * @param string $number
     * @param bool   $shippingFree
     */
    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $shippingFree = true
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $product['mainDetail']['shippingFree'] = $shippingFree;

        return $product;
    }
}
