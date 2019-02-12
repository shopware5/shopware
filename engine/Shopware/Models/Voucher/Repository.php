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

namespace Shopware\Models\Voucher;

use Doctrine\ORM\Query;
use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the Voucher model (Shopware\Models\Voucher\Voucher).
 * <br>
 * The voucher model repository is responsible to load all voucher data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of voucher
     * codes for the passed voucher id.
     *
     * @param int|null                                     $voucherId
     * @param string|null                                  $filter
     * @param string|\Doctrine\ORM\Query\Expr\OrderBy|null $order
     * @param int|null                                     $offset
     * @param int|null                                     $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getVoucherCodeListQuery($voucherId = null, $filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = $this->getVoucherCodeListQueryBuilder($voucherId, $filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVoucherCodeListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int|null                                     $voucherId
     * @param string|null                                  $filter
     * @param string|\Doctrine\ORM\Query\Expr\OrderBy|null $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVoucherCodeListQueryBuilder($voucherId = null, $filter = null, $order = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'codes.id as id',
            'codes.customerId as customerId',
            'codes.code as code',
            'codes.cashed as cashed',
            'customer.firstname as firstName',
            'customer.lastname as lastName',
            'customer.number as number',
        ]);
        $builder->from(\Shopware\Models\Voucher\Code::class, 'codes')
            ->leftJoin('codes.customer', 'customer')
            ->where('codes.voucherId = ?1')
            ->setParameter(1, $voucherId);
        // Search for values
        if (!empty($filter)) {
            $builder->andWhere('codes.code LIKE ?2')
                ->orWhere('customer.firstname LIKE ?2')
                ->orWhere('customer.lastname LIKE ?2')
                ->orWhere('customer.number LIKE ?2')
                ->setParameter(2, '%' . $filter . '%');
        }
        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select
     * the count of voucher codes for the passed voucher id.
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getVoucherCodeCountQuery($voucherId)
    {
        $builder = $this->getVoucherCodeCountQueryBuilder($voucherId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVoucherCodeCountQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVoucherCodeCountQueryBuilder($voucherId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([$builder->expr()->count('code.id') . 'as countCode'])
            ->from(\Shopware\Models\Voucher\Code::class, 'code')
            ->where('code.voucherId = ?1')
            ->setParameter(1, $voucherId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which...
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getVoucherCodeDeleteByVoucherIdQuery($voucherId)
    {
        $builder = $this->getVoucherCodeDeleteByVoucherIdQueryBuilder($voucherId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVoucherCodeDeleteByVoucherIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVoucherCodeDeleteByVoucherIdQueryBuilder($voucherId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(\Shopware\Models\Voucher\Code::class, 'code')
            ->where('code.voucherId = ?1')
            ->setMaxResults(10000)
            ->setParameter(1, $voucherId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select all data for the passed
     * voucher id.
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getVoucherDetailQuery($voucherId)
    {
        $builder = $this->getVoucherDetailQueryBuilder($voucherId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVoucherDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $voucherId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVoucherDetailQueryBuilder($voucherId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['vouchers', 'attribute'])
            ->from(\Shopware\Models\Voucher\Voucher::class, 'vouchers')
            ->leftJoin('vouchers.attribute', 'attribute')
            ->where('vouchers.id = ?1')
            ->setParameter(1, $voucherId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for vouchers
     * with the passed voucher code. The passed voucher id will be excluded.
     *
     * @param string $code
     * @param int    $voucherId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getValidateVoucherCodeQuery($code, $voucherId = null)
    {
        $builder = $this->getValidateVoucherCodeQueryBuilder($code, $voucherId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateVoucherCodeQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $code
     * @param int    $voucherId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateVoucherCodeQueryBuilder($code, $voucherId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['voucher'])
            ->from($this->getEntityName(), 'voucher')
            ->where('voucher.voucherCode = ?1')
            ->setParameter(1, $code);
        if (!empty($voucherId)) {
            $builder->andWhere('voucher.id != ?2')
                ->setParameter(2, $voucherId);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search vouchers
     * with the passed code. The passed voucher id will be excluded.
     *
     * @param string $code
     * @param int    $voucherId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getValidateOrderCodeQuery($code, $voucherId = null)
    {
        $builder = $this->getValidateOrderCodeQueryBuilder($code, $voucherId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateVoucherCodeQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $code
     * @param int    $voucherId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateOrderCodeQueryBuilder($code, $voucherId = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['voucher'])
            ->from($this->getEntityName(), 'voucher')
            ->where('voucher.orderCode = ?1')
            ->setParameter(1, $code);
        if (!empty($voucherId)) {
            $builder->andWhere('voucher.id != :voucherId')->setParameter('voucherId', $voucherId);
        }

        return $builder;
    }
}
