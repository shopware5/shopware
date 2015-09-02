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

use Shopware\Bundle\SearchBundleDBAL\SortingHandlerInterface;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\SortingHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PopularitySortingHandler implements SortingHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsSorting(SortingInterface $sorting)
    {
        return ($sorting instanceof PopularitySorting);
    }

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param SortingInterface|PopularitySorting $sorting
     * @param QueryBuilder $query
     * @param ShopContextInterface $context
     * @return void
     */
    public function generateSorting(
        SortingInterface $sorting,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $query->leftJoin(
            'product',
            's_articles_top_seller_ro',
            'topSeller',
            'topSeller.article_id = product.id'
        );

        $query->addOrderBy('topSeller.sales', $sorting->getDirection())
            ->addOrderBy('topSeller.article_id', $sorting->getDirection());
    }
}
