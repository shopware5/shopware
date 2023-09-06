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

use Shopware\Bundle\SearchBundle\Condition\ImmediateDeliveryCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ImmediateDeliveryConditionTest extends TestCase
{
    public function testNoStock(): void
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            [
                'first' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'second' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'third' => ['inStock' => 2, 'minPurchase' => 1, 'createVariants' => false],
                'fourth' => ['inStock' => 1, 'minPurchase' => 1, 'createVariants' => false],
            ],
            ['third', 'fourth'],
            null,
            [$condition]
        );
    }

    public function testMinPurchaseEquals(): void
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            [
                'first' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'second' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'third' => ['inStock' => 3, 'minPurchase' => 3, 'createVariants' => false],
                'fourth' => ['inStock' => 20, 'minPurchase' => 20, 'createVariants' => false],
            ],
            ['third', 'fourth'],
            null,
            [$condition]
        );
    }

    public function testSubVariantWithStock(): void
    {
        $condition = new ImmediateDeliveryCondition();

        $this->search(
            [
                'first' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'second' => ['inStock' => 0, 'minPurchase' => 1, 'createVariants' => false],
                'third' => ['inStock' => 1, 'minPurchase' => 1, 'createVariants' => false],
                'fourth' => ['inStock' => 1, 'minPurchase' => 1, 'createVariants' => true],
                'fifth' => ['inStock' => 2, 'minPurchase' => 1, 'createVariants' => false],
            ],
            ['third', 'fifth'],
            null,
            [$condition]
        );
    }

    /**
     * @param array<string, int> $data
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $data = ['inStock' => 0, 'minPurchase' => 1]
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $product['lastStock'] = true;
        $product['mainDetail'] = array_merge($product['mainDetail'], $data);

        return $product;
    }

    /**
     * @param array<string, mixed> $additionally
     */
    protected function createProduct(
        string $number,
        ShopContext $context,
        Category $category,
        $additionally
    ): Article {
        if ($additionally['createVariants'] === true) {
            $fourth = $this->getProduct($number, $context, $category);
            $configurator = $this->helper->getConfigurator(
                $context->getCurrentCustomerGroup(),
                $number
            );

            $fourth = array_merge($fourth, $configurator);
            foreach ($fourth['variants'] as &$variant) {
                $variant['inStock'] = 4;
                $variant['minPurchase'] = 1;
            }

            return $this->helper->createProduct($fourth);
        }

        return parent::createProduct(
            $number,
            $context,
            $category,
            $additionally
        );
    }
}
