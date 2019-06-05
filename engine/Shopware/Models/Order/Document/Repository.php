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

namespace Shopware\Models\Order\Document;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the order document model (Shopware\Models\Order\Document\Document).
 *
 * The order document model repository is responsible to load all document data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which contains
     * all required fields for the backend order document list.
     * The filtering is performed on all columns.
     * The passed limit parameters for the list paging are placed directly into the query object.
     * To determine the total number of records, use the following syntax:
     * Shopware()->Models()->getQueryCount($query);
     *
     * @param int        $orderId
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($orderId, $filter = null, $orderBy = null, $limit = null, $offset = null)
    {
        /** @var \Doctrine\ORM\QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder = $this->selectListQuery($builder);

        $builder = $this->filterListQuery($builder, $filter);
        $this->addOrderBy($builder, $orderBy);

        $builder->andWhere('documents.orderId = :orderId')
            ->setParameter('orderId', $orderId);

        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function which sets the fromPath and the selectPath for the order list query.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function selectListQuery(\Doctrine\ORM\QueryBuilder $builder)
    {
        //select the different entities
        $builder->select([
            'documents.id as id',
            'documents.date as date',
            'documents.typeId as typeId',
            'documents.customerId as customerId',
            'documents.orderId as orderId',
            'documents.amount as amount',
            'documents.documentId as documentId',
            'documents.hash as hash',
            'type.name as typeName',
        ]);

        //join the required tables for the order list
        $builder->from('Shopware\Models\Order\Document\Document', 'documents')
                ->join('documents.type', 'type');

        return $builder;
    }

    protected function filterListQuery(\Doctrine\ORM\QueryBuilder $builder, $filter = null)
    {
        return $builder;
    }
}
