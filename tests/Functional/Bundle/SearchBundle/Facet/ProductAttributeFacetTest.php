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

use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ProductAttributeFacetTest extends TestCase
{
    public function testEmptyFilesGetFilteredOut(): void
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_GTE,
            10
        );

        $result = $this->search(
            [
                'e1' => ['attr1' => 10, 'attr2' => ''],
                'e2' => ['attr1' => 20, 'attr2' => 'Test1'],
                'e3' => ['attr1' => 30, 'attr2' => 'Test2'],
            ],
            ['e1', 'e2', 'e3'],
            null,
            [$condition],
            [new ProductAttributeFacet('attr2', ProductAttributeFacet::MODE_VALUE_LIST_RESULT, 'asd', 'Test')]
        );

        $attributeFacet = $result->getFacets()[0];
        static::assertInstanceOf(ValueListFacetResult::class, $attributeFacet);
        static::assertCount(2, $attributeFacet->getValues());
        static::assertSame('Test1', $attributeFacet->getValues()[0]->getLabel());
        static::assertSame('Test2', $attributeFacet->getValues()[1]->getLabel());
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        Category $category = null,
        $attribute = ['attr1' => 10]
    ): array {
        $product = parent::getProduct($number, $context, $category);
        $product['mainDetail']['attribute'] = $attribute;

        return $product;
    }
}
