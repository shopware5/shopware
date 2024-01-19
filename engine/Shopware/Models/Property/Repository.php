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

namespace Shopware\Models\Property;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\OrderBy;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\Query\SqlWalker\ForceIndexWalker;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Attribute\PropertyGroup;

/**
 * @extends ModelRepository<Group>
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to get property relations
     *
     * @param array|null          $filter
     * @param string|OrderBy|null $order
     * @param int|null            $limit
     * @param int|null            $offset
     *
     * @return Query<Relation>
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
     *
     * @param array|null          $filter
     * @param string|OrderBy|null $order
     *
     * @return QueryBuilder
     */
    public function getPropertyRelationQueryBuilder($filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'relations',
        ]);
        $builder->from(Relation::class, 'relations')
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
     * @param array|null                $filter
     * @param array|string|OrderBy|null $order
     * @param int                       $limit
     * @param int                       $offset
     *
     * @return Query<Group>
     */
    public function getListGroupsQuery($filter = null, $order = null, $limit = null, $offset = null)
    {
        // Get the query and prepare the limit statement
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
     *
     * @param array|null          $filter
     * @param string|OrderBy|null $order
     *
     * @return QueryBuilder
     */
    public function getListGroupsQueryBuilder($filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'groups',
            'options',
            'attributes',
        ]);
        $builder->from(Group::class, 'groups')
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
     * @param int   $offset
     * @param int   $limit
     * @param array $filter
     *
     * @return Query<Group>
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
     *
     * @param array $filter
     *
     * @return QueryBuilder
     */
    public function getSetsQueryBuilder($filter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'attribute'])
                ->from(Group::class, 'groups')
                ->leftJoin('groups.attribute', 'attribute')
                ->orderBy('groups.position');

        if (!empty($filter[0]['value'])) {
            $builder->andWhere('groups.name LIKE :filter')
                    ->setParameter('filter', '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all property options
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $filter
     *
     * @return Query<Option>
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

        return $this->getForceIndexQuery($builder->getQuery(), 'get_options_query');
    }

    /**
     * Helper function to create the query builder for the "getOptionsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array $filter
     *
     * @return QueryBuilder
     */
    public function getOptionsQueryBuilder($filter)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['options'])
                ->from(Option::class, 'options')
                ->orderBy('options.name');

        if (!empty($filter[0]['value'])) {
            $builder->where('options.name LIKE :filter')
                    ->setParameter('filter', '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all property set assignments
     *
     * @param int $setId
     *
     * @return Query<Option>
     */
    public function getSetAssignsQuery($setId)
    {
        /** @var Query<Option> $query */
        $query = $this->getSetAssignsQueryBuilder($setId)->getQuery();

        return $this->getForceIndexQuery($query, null, true);
    }

    /**
     * Helper function to create the query builder for the "getSetAssignsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $setId
     *
     * @return QueryBuilder
     */
    public function getSetAssignsQueryBuilder($setId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'options.id',
            'options.name',
            'relations.groupId',
            'relations.position',
        ]);

        $builder->from(Option::class, 'options')
                ->innerJoin('options.relations', 'relations')
                ->where('relations.groupId = :id')
                ->setParameter('id', $setId)
                ->orderBy('relations.position');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the
     * property attributes for the passed group id.
     *
     * @param int $groupId
     *
     * @return Query<PropertyGroup>
     */
    public function getAttributesQuery($groupId)
    {
        $builder = $this->getAttributesQueryBuilder($groupId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $groupId
     *
     * @return QueryBuilder
     */
    public function getAttributesQueryBuilder($groupId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
            ->from(PropertyGroup::class, 'attribute')
            ->where('attribute.propertyGroupId = ?1')
            ->setParameter(1, $groupId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select
     * all data about the passed group id.
     *
     * @param int $groupId
     *
     * @return Query<Group>
     */
    public function getGroupDetailQuery($groupId)
    {
        $builder = $this->getGroupDetailQueryBuilder($groupId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getGroupDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $groupId
     *
     * @return QueryBuilder
     */
    public function getGroupDetailQueryBuilder($groupId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'attribute'])
                ->from(Group::class, 'groups')
                ->leftJoin('groups.attribute', 'attribute')
                ->where('groups.id = ?1')
                ->setParameter(1, $groupId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $optionId
     *
     * @return Query<Value>
     */
    public function getPropertyValueByOptionIdQuery($optionId)
    {
        $builder = $this->getPropertyValueByOptionIdQueryBuilder($optionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPropertyValueByOptionIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $optionId
     *
     * @return QueryBuilder
     */
    public function getPropertyValueByOptionIdQueryBuilder($optionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['value', 'media'])
                ->from(Value::class, 'value')
                ->leftJoin('value.media', 'media')
                ->where('value.optionId = ?0')
                ->orderBy('value.position', 'ASC')
                ->setParameter(0, $optionId);

        return $builder;
    }

    /**
     * Helper function to set the FORCE INDEX path.
     *
     * @param Query<Option> $query
     *
     * @return Query<Option>
     */
    private function getForceIndexQuery(Query $query, ?string $index = null, bool $straightJoin = false): Query
    {
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, ForceIndexWalker::class);
        if ($index !== null) {
            $query->setHint(ForceIndexWalker::HINT_FORCE_INDEX, $index);
        }
        if ($straightJoin) {
            $query->setHint(ForceIndexWalker::HINT_STRAIGHT_JOIN, true);
        }

        return $query;
    }
}
