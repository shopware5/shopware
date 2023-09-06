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

use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundleDBAL\SearchTerm\SearchIndexer;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;
use Zend_Cache_Core;

/**
 * @group elasticSearch
 */
class SearchTermConditionTest extends TestCase
{
    public function testSingleMatch(): void
    {
        $condition = new SearchTermCondition('Unit');

        $this->search(
            [
                'first' => 'Default Product',
                'second' => 'UnitTest Product',
                'third' => 'Custom Product',
            ],
            ['second'],
            null,
            [$condition]
        );
    }

    public function testMultipleMatch(): void
    {
        $condition = new SearchTermCondition('unit');

        $this->search(
            [
                'first' => 'Default Unit Product',
                'second' => 'Unit Test Product',
                'third' => 'Custom Product Unit',
                'fourth' => 'Custom produniuct',
            ],
            ['first', 'second', 'third'],
            null,
            [$condition]
        );
    }

    public function createProducts(array $products, ShopContext $context, Category $category): array
    {
        $articles = parent::createProducts($products, $context, $category);

        $this->getContainer()->get(SearchIndexer::class)->build();

        $this->getContainer()->get(Zend_Cache_Core::class)->clean('all', ['Shopware_Modules_Search']);

        return $articles;
    }

    /**
     * @param string $name
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $name = null
    ): array {
        $product = parent::getProduct($number, $context, $category);
        $product['name'] = $name;

        return $product;
    }
}
