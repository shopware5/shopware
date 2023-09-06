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

use Shopware\Bundle\SearchBundle\Condition\SimilarProductCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class SimilarProductConditionTest extends TestCase
{
    public function testSimilarProduct(): void
    {
        $main = $this->helper->createCategory(['name' => 'main']);
        $first = $this->helper->createCategory(['name' => 'first-category', 'parent' => $main]);
        $second = $this->helper->createCategory(['name' => 'second-category', 'parent' => $main]);

        $product = $this->getProduct('test', $this->getContext(), null, $second);
        $article = $this->helper->createProduct($product);
        $condition = new SimilarProductCondition($article->getId(), $article->getName());

        $this->search(
            [
                'one' => $first,
                'two' => $first,
                'three' => $first,
                'four' => $first,
                'five' => $first,
                'six' => $second,
                'seven' => $second,
                'eight' => $second,
                'nine' => $second,
                'ten' => $second,
            ],
            ['six', 'seven', 'eight', 'nine', 'ten'],
            $main,
            [$condition]
        );
    }

    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $additionally = null
    ): array {
        if ($additionally !== null) {
            static::assertInstanceOf(Category::class, $additionally);
        }

        return parent::getProduct($number, $context, $additionally);
    }
}
