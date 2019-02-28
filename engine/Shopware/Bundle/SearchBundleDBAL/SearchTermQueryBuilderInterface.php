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

namespace Shopware\Bundle\SearchBundleDBAL;

interface SearchTermQueryBuilderInterface
{
    /**
     * Creates the search query builder.
     * The query contains only the search expressions.
     *
     * Additionally conditions like category or prices are not included.
     *
     * Returned query builder should be possible to join as table.
     * Required table fields:
     *  - product_id : id of the product, used as join
     *
     * Returns null if no keywords or search tables are found
     *
     * @param string $term
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder|null
     */
    public function buildQuery($term);
}
