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

use Shopware\Components\Model\ModelRepository;

class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all filters for the listing
     *
     * @param string|null $filter
     *
     * @return \Doctrine\ORM\Query
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('filter')
            ->from('Shopware\Models\MultiEdit\Filter', 'filter');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all backup entities
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \Doctrine\ORM\Query
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getBackupListQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select('backup')
            ->from('Shopware\Models\MultiEdit\Backup', 'backup')
            ->orderBy('backup.date', 'DESC');

        return $builder;
    }
}
