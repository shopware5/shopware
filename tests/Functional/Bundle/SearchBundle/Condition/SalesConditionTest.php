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

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\SalesCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class SalesConditionTest extends TestCase
{
    public function testMinPurchaseUsesGreaterOrEqual(): void
    {
        $condition = new SalesCondition(100);

        $this->search(
            [
                'first' => ['sales' => 5],
                'second' => ['sales' => 100],
                'third' => ['sales' => 99],
                'fourth' => ['sales' => 101],
            ],
            ['second', 'fourth'],
            null,
            [$condition]
        );
    }

    public function createProducts(array $products, ShopContext $context, Category $category): array
    {
        $products = parent::createProducts($products, $context, $category);

        $topSeller = $this->getContainer()->get('topseller');
        $topSeller->incrementTopSeller($products['second']->getId(), 100);
        $topSeller->incrementTopSeller($products['fourth']->getId(), 101);

        return $products;
    }
}
