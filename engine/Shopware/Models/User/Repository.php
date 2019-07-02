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

namespace Shopware\Models\User;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the customer model (Shopware\Models\User\User).
 * <br>
 * The customer model repository is responsible to load all customer data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the user data for
     * the passed user id.
     *
     * @param int $userId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUserDetailQuery($userId)
    {
        $builder = $this->getUserDetailQueryBuilder($userId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUserDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getUserDetailQueryBuilder($userId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['users', 'attribute'])
                ->from(User::class, 'users')
                ->leftJoin('users.attribute', 'attribute')
                ->where('users.id = ?1')
                ->setParameter(1, $userId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of users.
     *
     * @param string|null $filter
     * @param int|null    $limit
     * @param int|null    $offset
     * @param array|null  $orderBy
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersQuery($filter = null, $limit = null, $offset = null, $orderBy = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getUsersQueryBuilder($filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        if ($orderBy !== null) {
            $builder->addOrderBy($orderBy);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUserListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string|null $filter
     *
     * @return QueryBuilder
     */
    public function getUsersQueryBuilder($filter = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
                'user.id as id',
                'user.username as username',
                'user.lastLogin as lastLogin',
                'user.name as name',
                'role.name as groupname',
                'user.active as active',
                'user.email as email',
            ]
        );
        $builder->from(User::class, 'user');
        $builder->join('user.role', 'role');
        if (!empty($filter)) {
            $builder->where('user.username LIKE ?1')
                    ->orWhere('user.name LIKE ?1')
                    ->setParameter(1, '%' . $filter . '%');
        }
        $builder->orderBy('username');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of roles.
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRolesQuery($offset = null, $limit = null)
    {
        $builder = $this->getRolesQueryBuilder();
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRolesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getRolesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['roles'])
                ->from(Role::class, 'roles');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes
     * for the passed user id.
     *
     * @param int $userId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($userId)
    {
        $builder = $this->getAttributesQueryBuilder($userId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getAttributesQueryBuilder($userId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
                ->from(\Shopware\Models\Attribute\User::class, 'attribute')
                ->where('attribute.userId = ?1')
                ->setParameter(1, $userId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of resources.
     *
     * @param string|null $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getResourcesQuery($filter = null)
    {
        $builder = $this->getResourcesQueryBuilder($filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getResourcesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string|null $filter
     *
     * @return QueryBuilder
     */
    public function getResourcesQueryBuilder($filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['resources', 'privileges', 'requirements'])
                ->from('Shopware\Models\User\Resource', 'resources')
                ->leftJoin('resources.privileges', 'privileges')
                ->leftJoin('privileges.requirements', 'requirements')
                ->orderBy('resources.name');

        if (!empty($filter)) {
            $builder->where('resources.name LIKE ?1')
                    ->setParameter(1, $filter . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all resources
     * with an active admin rule.
     *
     * @param int $roleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getResourcesWithAdminRuleQuery($roleId)
    {
        $builder = $this->getResourcesWithAdminRuleQueryBuilder($roleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getResourcesWithAdminRuleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $roleId
     *
     * @return QueryBuilder
     */
    public function getResourcesWithAdminRuleQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['rule.resourceId'])
                ->from(Rule::class, 'rule')
                ->where('rule.privilegeId IS NULL')
                ->andWhere('rule.resourceId IS NOT NULL')
                ->andWhere('rule.roleId = ?1')
                ->setParameter(1, $roleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all role data for the passed role id.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRoleDetailQuery()
    {
        $builder = $this->getRoleDetailQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRoleDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getRoleDetailQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['roles', 'rules', 'privilege'])
                ->from(Role::class, 'roles')
                ->leftJoin('roles.rules', 'rules')
                ->leftJoin('rules.privilege', 'privilege');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes the passed resource
     *
     * @param int $resourceId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getResourceDeleteQuery($resourceId)
    {
        $builder = $this->getResourceDeleteQueryBuilder($resourceId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getResourceDeleteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $resourceId
     *
     * @return QueryBuilder
     */
    public function getResourceDeleteQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Resource::class, 'resource')
                ->where('resource.id = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes the passed privilege.
     *
     * @param int $privilegeId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPrivilegeDeleteQuery($privilegeId)
    {
        $builder = $this->getPrivilegeDeleteQueryBuilder($privilegeId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPrivilegeDeleteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $privilegeId
     *
     * @return QueryBuilder
     */
    public function getPrivilegeDeleteQueryBuilder($privilegeId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Privilege::class, 'privilege')
                ->where('privilege.id = ?1')
                ->setParameter(1, $privilegeId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes all privileges
     * of the passed resource id.
     *
     * @param int $resourceId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPrivilegeDeleteByResourceIdQuery($resourceId)
    {
        $builder = $this->getPrivilegeDeleteByResourceIdQueryBuilder($resourceId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPrivilegeDeleteByResourceIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $resourceId
     *
     * @return QueryBuilder
     */
    public function getPrivilegeDeleteByResourceIdQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Privilege::class, 'privilege')
                ->where('privilege.resourceId = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which delete all rules
     * for the passed privilege id.
     *
     * @param int $privilegeId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRuleDeleteByPrivilegeIdQuery($privilegeId)
    {
        $builder = $this->getRuleDeleteByPrivilegeIdQueryBuilder($privilegeId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRuleDeleteByPrivilegeIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $privilegeId
     *
     * @return QueryBuilder
     */
    public function getRuleDeleteByPrivilegeIdQueryBuilder($privilegeId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Rule::class, 'rule')
                ->where('rule.privilegeId = ?1')
                ->setParameter(1, $privilegeId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which delete the passed rule.
     *
     * @param int $roleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRuleDeleteByRoleIdQuery($roleId)
    {
        $builder = $this->getRuleDeleteByRoleIdQueryBuilder($roleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRuleDeleteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $roleId
     *
     * @return QueryBuilder
     */
    public function getRuleDeleteByRoleIdQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Rule::class, 'rule')
                ->where('rule.roleId = ?1')
                ->setParameter(1, $roleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes all rules
     * for the passed resource id.
     *
     * @param int $resourceId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRuleDeleteByResourceIdQuery($resourceId)
    {
        $builder = $this->getRuleDeleteByResourceIdQueryBuilder($resourceId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRuleDeleteByResourceIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $resourceId
     *
     * @return QueryBuilder
     */
    public function getRuleDeleteByResourceIdQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Rule::class, 'rule')
                ->where('rule.resourceId = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $roleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAdminRuleDeleteQuery($roleId)
    {
        $builder = $this->getAdminRuleDeleteQueryBuilder($roleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAdminRuleDeleteQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $roleId
     *
     * @return QueryBuilder
     */
    public function getAdminRuleDeleteQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();
        $builder->delete(Rule::class, 'rule')
                ->where('rule.roleId = ?1')
                ->setParameter(1, $roleId)
                ->andWhere($expr->isNull('rule.resourceId'))
                ->andWhere($expr->isNull('rule.privilegeId'));

        return $builder;
    }
}
