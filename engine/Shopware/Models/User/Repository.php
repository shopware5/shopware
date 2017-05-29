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

namespace   Shopware\Models\User;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the customer model (Shopware\Models\Customer\Customer).
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
     * @param $userId
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
     * @param $userId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserDetailQueryBuilder($userId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['users', 'attribute'])
                ->from('Shopware\Models\User\User', 'users')
                ->leftJoin('users.attribute', 'attribute')
                ->where('users.id = ?1')
                ->setParameter(1, $userId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of users.
     *
     * @param null $filter
     * @param null $limit
     * @param null $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUsersQuery($filter = null, $limit = null, $offset = null)
    {
        $builder = $this->getUsersQueryBuilder($filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUserListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUsersQueryBuilder($filter = null)
    {
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
        $builder->from('Shopware\Models\User\User', 'user');
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
     * @param null $offset
     * @param null $limit
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRolesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['roles'])
                ->from('Shopware\Models\User\Role', 'roles');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes
     * for the passed user id.
     *
     * @param $userId
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
     * @param $userId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($userId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['attribute'])
                      ->from('Shopware\Models\Attribute\User', 'attribute')
                      ->where('attribute.userId = ?1')
                      ->setParameter(1, $userId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of resources.
     *
     * @param $filter
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
     * @param null $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getResourcesQueryBuilder($filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['resources', 'privileges'])
                ->from('Shopware\Models\User\Resource', 'resources')
                ->leftJoin('resources.privileges', 'privileges');

        if (!empty($filter)) {
            $builder->where($builder->expr()->like('resources.name', '?1'))
                    ->setParameter(1, $filter . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all resources
     * with an active admin rule.
     *
     * @param $roleId
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
     * @param $roleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getResourcesWithAdminRuleQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['rule.resourceId'])
                ->from('Shopware\Models\User\Rule', 'rule')
                ->where($builder->expr()->isNull('rule.privilegeId'))
                ->andWhere($builder->expr()->isNotNull('rule.resourceId'))
                ->andWhere($builder->expr()->eq('rule.roleId', '?1'))
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRoleDetailQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['roles', 'rules', 'privilege'])
                ->from('Shopware\Models\User\Role', 'roles')
                ->leftJoin('roles.rules', 'rules')
                ->leftJoin('rules.privilege', 'privilege');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes the passed resource
     *
     * @param $resourceId
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
     * @param $resourceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getResourceDeleteQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Resource', 'resource')
                ->where('resource.id = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes the passed privilege.
     *
     * @param $privilegeId
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
     * @param $privilegeId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPrivilegeDeleteQueryBuilder($privilegeId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Privilege', 'privilege')
                ->where('privilege.id = ?1')
                ->setParameter(1, $privilegeId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes all privileges
     * of the passed resource id.
     *
     * @param $resourceId
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
     * @param $resourceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPrivilegeDeleteByResourceIdQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Privilege', 'privilege')
                ->where('privilege.resourceId = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which delete all rules
     * for the passed privilege id.
     *
     * @param $privilegeId
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
     * @param $privilegeId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRuleDeleteByPrivilegeIdQueryBuilder($privilegeId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Rule', 'rule')
                ->where('rule.privilegeId = ?1')
                ->setParameter(1, $privilegeId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which delete the passed rule.
     *
     * @param $roleId
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
     * @param $roleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRuleDeleteByRoleIdQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Rule', 'rule')
                ->where('rule.roleId = ?1')
                ->setParameter(1, $roleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which deletes all rules
     * for the passed resource id.
     *
     * @param $resourceId
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
     * @param $resourceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRuleDeleteByResourceIdQueryBuilder($resourceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\User\Rule', 'rule')
                ->where('rule.resourceId = ?1')
                ->setParameter(1, $resourceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $roleId
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
     * @param $roleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminRuleDeleteQueryBuilder($roleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();
        $builder->delete('Shopware\Models\User\Rule', 'rule')
                ->where('rule.roleId = ?1')
                ->setParameter(1, $roleId)
                ->andWhere($expr->isNull('rule.resourceId'))
                ->andWhere($expr->isNull('rule.privilegeId'));

        return $builder;
    }
}
