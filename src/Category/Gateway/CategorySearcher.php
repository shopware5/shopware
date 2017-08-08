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

namespace Shopware\Category\Gateway;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Category\Struct\CategoryIdentity;
use Shopware\Context\Struct\TranslationContext;
use Shopware\Search\Criteria;
use Shopware\Search\Search;
use Shopware\Search\SearchResultInterface;

class CategorySearcher extends Search
{
    protected function createQuery(Criteria $criteria, TranslationContext $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'category.id',
            'category.parent as parent_id',
            'category.path',
            'category.active',
            'category.position',
        ]);

        $query->from('category', 'category');

        return $query;
    }

    protected function createResult(array $rows, int $total): SearchResultInterface
    {
        $rows = array_map(
            function (array $row) {
                return new CategoryIdentity(
                    (int) $row['id'],
                    $row['parent_id'] ? (int) $row['parent_id'] : null,
                    (int) $row['position'],
                    array_filter(explode('|', $row['path'])),
                    (bool) $row['active']
                );
            },
            $rows
        );

        return new CategorySearchResult($rows, $total);
    }
}
