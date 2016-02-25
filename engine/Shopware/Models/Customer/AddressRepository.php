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

namespace Shopware\Models\Customer;

use Shopware\Bundle\FormBundle\Forms\Account\AddressFormType;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the address model (Shopware\Models\Customer\Address).
 */
class AddressRepository extends ModelRepository
{
    /**
     * Returns a query-object for the billing address for a specified user
     *
     * @param null $userId
     * @return \Doctrine\ORM\Query
     */
    public function getByUserQuery($userId)
    {
        $builder = $this->getByUserQueryBuilder($userId);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getUserBillingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $userId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByUserQueryBuilder($userId)
    {
        $builder = $this->getListQueryBuilder();

        $builder->andWhere('IDENTITY(address.customer) = :userId')
            ->setParameter('userId', $userId);

        return $builder;
    }

    /**
     * Returns a query-object for the billing address for a specified user
     *
     * @param int $addressId
     * @param int $userId
     * @return \Doctrine\ORM\Query
     */
    public function getDetailByUserQuery($addressId, $userId)
    {
        $builder = $this->getDetailByUserQueryBuilder($addressId, $userId);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getUserBillingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $addressId
     * @param int $userId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailByUserQueryBuilder($addressId, $userId)
    {
        $builder = $this->getDetailQueryBuilder($addressId);

        $builder->andWhere('IDENTITY(address.customer) = :userId')
            ->setParameter('userId', $userId);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select the manufacturer detail information based on the manufacturer id
     * Used for detail information in the backend module.
     *
     * @param int $addressId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($addressId)
    {
        $builder = $this->getDetailQueryBuilder($addressId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $addressId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($addressId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'address',
            'country',
            'state'
        ]);

        $builder->from('Shopware\Models\Customer\Address', 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'state')
            ->where('address.id = :addressId')
            ->setParameter('addressId', $addressId);

        return $builder;
    }

    /**
     * Returns the \Doctrine\ORM\Query to select all manufacturers for example for the backend tree
     *
     * @param array $filterBy
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery(array $filterBy = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $builder = $this->getListQueryBuilder($filterBy, $orderBy, $limit, $offset);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param   array $filterBy
     * @param   array $orderBy
     * @param   null  $limit
     * @param   null  $offset
     *
     * @return  \Doctrine\ORM\Query
     */
    public function getListQueryBuilder(array $filterBy = [], array $orderBy = [], $limit = null, $offset = null)
    {
        /**@var $builder \Shopware\Components\Model\QueryBuilder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'address',
            'country',
            'state'
        ]);

        $builder->from('Shopware\Models\Customer\Address', 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'state');

        if (!empty($filterBy)) {
            $builder->addFilter($filterBy);
        }

        if (!empty($orderBy)) {
            $builder->addOrderBy($orderBy);
        }

        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }
}
