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

use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class ManufacturerConditionTest extends TestCase
{
    public function testSingleManufacturer(): void
    {
        $manufacturer = $this->helper->createManufacturer();
        $condition = new ManufacturerCondition([$manufacturer->getId()]);

        $this->search(
            [
                'first' => $manufacturer,
                'second' => $manufacturer,
                'third' => null,
            ],
            ['first', 'second'],
            null,
            [$condition]
        );
    }

    public function testMultipleManufacturers(): void
    {
        $manufacturer = $this->helper->createManufacturer();
        $second = $this->helper->createManufacturer();

        $condition = new ManufacturerCondition([
            $manufacturer->getId(),
            $second->getId(),
        ]);

        $this->search(
            [
                'first' => $manufacturer,
                'second' => $second,
                'third' => null,
            ],
            ['first', 'second'],
            null,
            [$condition]
        );
    }

    /**
     * @param Supplier|null $manufacturer
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $manufacturer = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        if ($manufacturer) {
            $product['supplierId'] = $manufacturer->getId();
        }

        return $product;
    }
}
