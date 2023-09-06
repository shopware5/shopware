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

use Shopware\Bundle\SearchBundle\Condition\CustomerGroupCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

/**
 * @group elasticSearch
 */
class CustomerGroupConditionTest extends TestCase
{
    public function testSingleCustomerGroup(): void
    {
        $customerGroup = $this->helper->createCustomerGroup(['key' => 'CON']);

        $this->search(
            [
                'first' => [$customerGroup],
                'second' => [$customerGroup],
                'third' => null,
                'fourth' => null,
            ],
            ['third', 'fourth'],
            null,
            [new CustomerGroupCondition([$customerGroup->getId()])]
        );
    }

    public function testMultipleCustomerGroups(): void
    {
        $first = $this->helper->createCustomerGroup(['key' => 'CON']);
        $second = $this->helper->createCustomerGroup(['key' => 'CON2']);

        $condition = new CustomerGroupCondition([$first->getId(), $second->getId()]);

        $this->search(
            [
                'first' => [$first],
                'second' => [$second],
                'third' => [$first, $second],
                'fourth' => null,
            ],
            ['fourth'],
            null,
            [$condition]
        );
    }

    /**
     * @param Group[]|null $customerGroups
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        ?Category $category = null,
        $customerGroups = null
    ): array {
        $product = parent::getProduct($number, $context, $category);

        $product['customerGroups'] = [];
        if ($customerGroups !== null) {
            foreach ($customerGroups as $customerGroup) {
                $product['customerGroups'][] = ['id' => $customerGroup->getId()];
            }
        }

        return $product;
    }
}
