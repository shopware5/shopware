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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the address model (Shopware\Models\Customer\Address).
 */
class AddressRepository extends ModelRepository
{
    /**
     * Returns a query-object for the billing address for a specified user
     *
     * @param int $userId
     *
     * @return array
     */
    public function getListArray($userId)
    {
        return $this->getByUserQueryBuilder($userId)
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery(array $criteria = [], array $orderBy = [], $limit = 25, $offset = 0)
    {
        return $this->getListQueryBuilder($criteria, $orderBy, $limit, $offset)->getQuery();
    }

    /**
     * @param int $addressId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOne($addressId)
    {
        return $this->getDetailQueryBuilder($addressId)->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getUserBillingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $addressId
     * @param int $userId
     *
     * @return Address
     */
    public function getOneByUser($addressId, $userId)
    {
        $builder = $this->getDetailQueryBuilder($addressId);

        return $builder
            ->andWhere('IDENTITY(address.customer) = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Helper method to create the query builder for the "getUserBillingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $userId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getByUserQueryBuilder($userId)
    {
        $builder = $this->getListQueryBuilder();

        $builder
            ->andWhere('IDENTITY(address.customer) = :userId')
            ->setParameter('userId', $userId)
            ->join('address.customer', 'customer')
            ->addSelect([
                '(CASE WHEN (customer.defaultBillingAddress = address.id) THEN 1 ELSE 0 END) as HIDDEN isDefaultBillingAddress',
                '(CASE WHEN (customer.defaultShippingAddress = address.id) THEN 1 ELSE 0 END) as HIDDEN isDefaultShippingAddress',
            ])
            ->addOrderBy('isDefaultBillingAddress', 'DESC')
            ->addOrderBy('isDefaultShippingAddress', 'DESC');

        return $builder;
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $addressId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getDetailQueryBuilder($addressId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'address',
            'customer',
            'attribute',
            'country',
            'state',
        ]);

        $builder->from(\Shopware\Models\Customer\Address::class, 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'state')
            ->leftJoin('address.attribute', 'attribute')
            ->join('address.customer', 'customer')
            ->where('address.id = :addressId')
            ->setParameter('addressId', $addressId);

        return $builder;
    }

    /**
     * Helper method to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getListQueryBuilder(array $filterBy = [], array $orderBy = [], $limit = null, $offset = null)
    {
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'address',
            'attribute',
            'country',
            'state',
        ]);

        $builder->from(\Shopware\Models\Customer\Address::class, 'address')
            ->leftJoin('address.country', 'country')
            ->leftJoin('address.state', 'state')
            ->leftJoin('address.attribute', 'attribute');

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
