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

namespace Shopware\Models\Property;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\Query\SqlWalker;

/**
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to get property relations
     * @param $filter
     * @param $order
     * @param $limit
     * @param $offset
     * @return \Doctrine\ORM\Query
     */
    public function getPropertyRelationQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        $builder = $this->getPropertyRelationQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPropertyRelationQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @param $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPropertyRelationQueryBuilder($filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'relations'
        ));
        $builder->from('Shopware\Models\Property\Relation', 'relations')
            ->leftJoin('relations.option', 'options')
            ->leftJoin('relations.group', 'groups');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Receives all known property groups
     *
     * @param null $filter
     * @param null $order
     * @param null $limit
     * @param null $offset
     * @return \Doctrine\ORM\Query
     */
    public function getListGroupsQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // get the query and prepare the limit statement
        $builder = $this->getListGroupsQueryBuilder($filter, $order);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                   ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $filter
     * @param null $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListGroupsQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'groups',
            'options',
            'attributes'
        ));
        $builder->from('Shopware\Models\Property\Group', 'groups')
            ->leftJoin('groups.options', 'options')
            ->leftJoin('groups.attribute', 'attributes');

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all property sets
     *
     * @param $offset
     * @param $limit
     * @param $filter
     * @return \Doctrine\ORM\Query
     */
    public function getSetsQuery($offset, $limit, $filter)
    {
        $builder = $this->getSetsQueryBuilder($filter);

        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSetsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSetsQueryBuilder($filter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('groups', 'attribute'))
                ->from('Shopware\Models\Property\Group', 'groups')
                ->leftJoin('groups.attribute', 'attribute')
                ->orderBy('groups.position');

        if (!empty($filter[0]["value"])) {
            $builder->andWhere('groups.name LIKE :filter')
                    ->setParameter('filter', '%' . $filter[0]["value"] . '%');
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all property options
     *
     * @param $offset
     * @param $limit
     * @param $filter
     * @return \Doctrine\ORM\Query
     */
    public function getOptionsQuery($offset, $limit, $filter)
    {
        $builder = $this->getOptionsQueryBuilder($filter);

        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }
        return $this->getForceIndexQuery($builder->getQuery(), 'get_options_query', false);
    }

    /**
     * Helper function to create the query builder for the "getOptionsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOptionsQueryBuilder($filter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('options'))
                ->from('Shopware\Models\Property\Option', 'options')
                ->orderBy('options.name');

        if (!empty($filter[0]["value"])) {
            $builder->where('options.name LIKE :filter')
                    ->setParameter('filter', '%' . $filter[0]["value"] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all property set assignments
     *
     * @param $setId
     * @return \Doctrine\ORM\Query
     */
    public function getSetAssignsQuery($setId)
    {
        $builder = $this->getSetAssignsQueryBuilder($setId);
        return $this->getForceIndexQuery($builder->getQuery(), null, true);
    }

    /**
     * Helper function to create the query builder for the "getSetAssignsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $setId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSetAssignsQueryBuilder($setId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'options.id',
            'options.name',
            'relations.groupId',
            'relations.position'
        ));

        $builder->from('Shopware\Models\Property\Option', 'options')
                ->innerJoin('options.relations', 'relations')
                ->where('relations.groupId = :id')
                ->setParameter('id', $setId)
                ->orderBy('relations.position');

        return $builder;
    }

    /**
     * Helper function to set the FORCE INDEX path.
     * @param $query \Doctrine\ORM\Query
     * @param $index String
     * @param bool $straightJoin
     * @return \Doctrine\ORM\Query
     */
    private function getForceIndexQuery($query, $index = null, $straightJoin = false)
    {
        $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Shopware\Components\Model\Query\SqlWalker\ForceIndexWalker');
        if ($index !== null) {
            $query->setHint(SqlWalker\ForceIndexWalker::HINT_FORCE_INDEX, $index);
        }
        if ($straightJoin) {
            $query->setHint(SqlWalker\ForceIndexWalker::HINT_STRAIGHT_JOIN, true);
        }
        return $query;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the
     * property attributes for the passed group id.
     * @param $groupId
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($groupId)
    {
        $builder = $this->getAttributesQueryBuilder($groupId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $groupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($groupId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\PropertyGroup', 'attribute')
                      ->where('attribute.propertyGroupId = ?1')
                      ->setParameter(1, $groupId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select
     * all data about the passed group id.
     * @param $groupId
     * @return \Doctrine\ORM\Query
     */
    public function getGroupDetailQuery($groupId)
    {
        $builder = $this->getGroupDetailQueryBuilder($groupId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getGroupDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $groupId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGroupDetailQueryBuilder($groupId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('groups', 'attribute'))
                ->from('Shopware\Models\Property\Group', 'groups')
                ->leftJoin('groups.attribute', 'attribute')
                ->where('groups.id = ?1')
                ->setParameter(1, $groupId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $optionId
     * @return \Doctrine\ORM\Query
     */
    public function getPropertyValueByOptionIdQuery($optionId)
    {
        $builder = $this->getPropertyValueByOptionIdQueryBuilder($optionId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPropertyValueByOptionIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $optionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPropertyValueByOptionIdQueryBuilder($optionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('value', 'media'))
                ->from('Shopware\Models\Property\Value', 'value')
                ->leftJoin('value.media', 'media')
                ->where('value.optionId = ?0')
                ->orderBy('value.position', 'ASC')
                ->setParameter(0, $optionId);

        return $builder;
    }
}
