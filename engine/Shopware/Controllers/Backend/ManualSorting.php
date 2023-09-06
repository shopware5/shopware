<?php
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

namespace Shopware\Controllers\Backend;

use Shopware\Bundle\StoreFrontBundle\Struct\Search\CustomSorting;
use Shopware\Models\Shop\Shop;
use Shopware_Controllers_Backend_ExtJs;

class ManualSorting extends Shopware_Controllers_Backend_ExtJs
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->get(\Shopware\Components\ShopRegistrationServiceInterface::class)->registerShop($this->getModelManager()->getRepository(Shop::class)->getActiveDefault());
    }

    public function listAction(int $start = 0, int $limit = 25, int $categoryId = 3, int $sortingId = 1): void
    {
        $sorting = $this->getSorting($categoryId, $sortingId);

        $products = $this->get(\Shopware\Components\ManualSorting\ProductLoaderInterface::class)->load($categoryId, $start, $limit, $sorting);

        $this->View()->assign($products);
    }

    public function assignPositionAction(int $categoryId = 3, int $sortingId = 1, array $data = []): void
    {
        $this->get(\Shopware\Components\ManualSorting\PositionServiceInterface::class)
            ->assign($categoryId, $sortingId, $data);
    }

    public function removePositionAction(int $categoryId = 3, array $data = []): void
    {
        $this->get(\Doctrine\DBAL\Connection::class)->delete('s_categories_manual_sorting', [
            'category_id' => $categoryId,
            'product_id' => $data['id'],
        ]);
    }

    public function resetCategoryAction(int $categoryId): void
    {
        $this->get(\Doctrine\DBAL\Connection::class)->delete('s_categories_manual_sorting', [
            'category_id' => $categoryId,
        ]);
    }

    public function getSortingsAction(int $categoryId): void
    {
        $this->View()->assign('success', true);
        $this->View()->assign('data', array_values(array_map(static function (CustomSorting $sorting) {
            return [
                'id' => $sorting->getId(),
                'name' => $sorting->getLabel(),
            ];
        }, $this->getSortings($categoryId))));
    }

    /**
     * @return CustomSorting[]
     */
    private function getSortings(int $categoryId): array
    {
        $context = $this->get(\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::class)->getShopContext();

        return current($this->get(\Shopware\Bundle\StoreFrontBundle\Service\CustomSortingServiceInterface::class)->getSortingsOfCategories([$categoryId], $context));
    }

    private function getSorting(int $categoryId, int $sortingId): ?CustomSorting
    {
        foreach ($this->getSortings($categoryId) as $sorting) {
            if ($sorting->getId() === $sortingId) {
                return $sorting;
            }
        }

        return null;
    }
}
