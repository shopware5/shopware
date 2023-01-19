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

namespace Shopware\Models\MultiEdit;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * @extends ModelRepository<Backup|Filter|Queue|QueueArticle>
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all filters for the listing
     *
     * @param string|null $filter
     *
     * @return Query<Filter>
     */
    public function getListQuery($filter = null)
    {
        $builder = $this->getListQueryBuilder($filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string|null $filter
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder($filter)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('filter')
            ->from(Filter::class, 'filter');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all backup entities
     *
     * @param int|null $offset
     * @param int      $limit
     *
     * @return Query<Backup>
     */
    public function getBackupListQuery($offset, $limit)
    {
        $builder = $this->getBackupListQueryBuilder();

        if ($offset !== null) {
            $builder->setFirstResult($offset);
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getBackupListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getBackupListQueryBuilder()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('backup')
            ->from(Backup::class, 'backup')
            ->orderBy('backup.date', 'DESC');
    }
}
