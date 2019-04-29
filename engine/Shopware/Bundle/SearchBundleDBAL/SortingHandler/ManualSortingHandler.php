<?php
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

namespace Shopware\Bundle\SearchBundleDBAL\SortingHandler;

use Shopware\Bundle\SearchBundle\Sorting\ManualSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandler\CategoryConditionHandler;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ManualSortingHandler implements SortingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return $sorting instanceof ManualSorting;
    }

    /**
     * {@inheritdoc}
     */
    public function generateSorting(SortingInterface $sorting, QueryBuilder $query, ShopContextInterface $context)
    {
        if (!$query->hasState(CategoryConditionHandler::STATE_NAME)) {
            return;
        }

        $query
            ->leftJoin(CategoryConditionHandler::STATE_NAME, 's_categories_manual_sorting', 'manual_sorting', 'manual_sorting.product_id = product.id AND manual_sorting.category_id = ' . CategoryConditionHandler::STATE_NAME . '.categoryID')
            ->orderBy('IFNULL(manual_sorting.position, 99999)', $sorting->getDirection());
    }
}
