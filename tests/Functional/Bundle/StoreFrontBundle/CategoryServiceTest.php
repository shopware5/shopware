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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

class CategoryServiceTest extends TestCase
{
    public function testCategorySorting(): void
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId(), 'position' => 1]);
        $third = $this->helper->createCategory(['name' => 'third', 'parent' => $first->getId(), 'position' => 2]);
        $fourth = $this->helper->createCategory(['name' => 'fourth', 'parent' => $first->getId(), 'position' => 2]);

        $categories = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface::class)->getList(
            [
                $second->getId(),
                $third->getId(),
                $fourth->getId(),
            ],
            $this->getContext()
        );

        foreach ($categories as $id => $category) {
            static::assertEquals($id, $category->getId());
        }

        $categories = array_values($categories);
        static::assertEquals($second->getId(), $categories[0]->getId());
        static::assertEquals($third->getId(), $categories[1]->getId());
        static::assertEquals($fourth->getId(), $categories[2]->getId());
    }

    public function testBlockedCustomerGroups(): void
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId()]);
        $third = $this->helper->createCategory(['name' => 'third',   'parent' => $second->getId()]);

        $context = $this->getContext();

        Shopware()->Db()->query(
            'INSERT INTO s_categories_avoid_customergroups (categoryID, customerGroupID) VALUES (?, ?)',
            [$second->getId(), $context->getCurrentCustomerGroup()->getId()]
        );
        Shopware()->Db()->query(
            'INSERT INTO s_categories_avoid_customergroups (categoryID, customerGroupID) VALUES (?, ?)',
            [$third->getId(), $context->getCurrentCustomerGroup()->getId()]
        );

        $categories = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface::class)->getList(
            [
                $first->getId(),
                $second->getId(),
                $third->getId(),
            ],
            $context
        );

        static::assertCount(1, $categories);

        static::assertArrayHasKey($first->getId(), $categories);
    }

    public function testOnlyActiveCategories(): void
    {
        $first = $this->helper->createCategory(['name' => 'first',  'parent' => 3, 'active' => false]);
        $second = $this->helper->createCategory(['name' => 'second', 'parent' => $first->getId(), 'active' => false]);
        $third = $this->helper->createCategory(['name' => 'third',   'parent' => $second->getId()]);

        $categories = Shopware()->Container()->get(\Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface::class)->getList(
            [
                $first->getId(),
                $second->getId(),
                $third->getId(),
            ],
            $this->getContext()
        );

        static::assertCount(1, $categories);
        static::assertArrayHasKey($third->getId(), $categories);
    }
}
