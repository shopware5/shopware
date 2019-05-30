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

namespace Shopware\Models\Partner;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Repository for the Partner model (Shopware\Models\Partner\Partner).
 *
 * The Partner model repository is responsible to load all Partner data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the partners for the backend list
     *
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($order = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($order);
        if (!empty($offset)) {
            $builder->setFirstResult($offset);
        }
        if (!empty($limit)) {
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array $order
     *
     * @return QueryBuilder
     */
    public function getListQueryBuilder($order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'partner.id',
                'partner.company as company',
                'partner.active as active',
                'partner.date as date',
                'partner.idCode as idCode',
                '(' . $this->getDatePartListDQL('o') . ') as yearlyAmount',
                '(' . $this->getDatePartListDQL('om', true) . ') as monthlyAmount',
            ])
            ->from(\Shopware\Models\Partner\Partner::class, 'partner');

        if (!empty($order)) {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select the partner for the detail page
     *
     * @param array $filter
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailQuery($filter)
    {
        $builder = $this->getDetailQueryBuilder($filter);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailQueryBuilder($filter)
    {
        $builder = $this->createQueryBuilder('partner');
        $builder->select(['partner'])
                ->addFilter($filter);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object to select the partner for the statistic list
     *
     * @param array|null         $order
     * @param int|null           $offset
     * @param int|null           $limit
     * @param int                $partnerId
     * @param bool               $summary
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param float|int          $userCurrencyFactor
     *
     * @return \Doctrine\ORM\Query
     */
    public function getStatisticListQuery($order, $offset, $limit, $partnerId, $summary, $fromDate, $toDate, $userCurrencyFactor = 1)
    {
        $builder = $this->getStatisticListQueryBuilder($order, $partnerId, $summary, $fromDate, $toDate, $userCurrencyFactor);
        if (!$summary && !empty($limit)) {
            $builder->setFirstResult($offset);
            $builder->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getStatisticListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array|null         $order
     * @param int                $partnerId
     * @param bool               $summary
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param float|int          $userCurrencyFactor
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStatisticListQueryBuilder($order, $partnerId, $summary, $fromDate, $toDate, $userCurrencyFactor = 1)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $expr = $this->getEntityManager()->getExpressionBuilder();

        $builder->select(
            [
                'o.orderTime as orderTime',
                'o.id as id',
                'o.number as number',
                'SUM((o.invoiceAmountNet - o.invoiceShippingNet) / (o.currencyFactor / :userCurrencyFactor)) as netTurnOver',
                'SUM((o.invoiceAmountNet - o.invoiceShippingNet) / (o.currencyFactor / :userCurrencyFactor) / 100 * partner.percent) as provision',
                'customer.email as customerEmail',
                'billing.company as customerCompany',
                'customer.firstname as customerFirstName',
                'customer.lastname as customerLastName',
                'customer.number as customerNumber',
                'orderState.name as orderStatus',
                'orderState.id as orderStatusId',
            ])
            ->from(\Shopware\Models\Order\Order::class, 'o')
            ->leftJoin('o.partner', 'partner')
            ->leftJoin('o.orderStatus', 'orderState')
            ->leftJoin('o.customer', 'customer')
            ->leftJoin('customer.defaultBillingAddress', 'billing')
            ->where('partner.id = ?1')
            ->andWhere('o.status != 4')
            ->andWhere('o.status != -1')
            ->andWhere('o.orderTime > ?2')
            ->andWhere('o.orderTime < ?3')
            ->setParameter(1, $partnerId)
            ->setParameter(2, $fromDate)
            ->setParameter(3, $toDate)
            ->setParameter('userCurrencyFactor', $userCurrencyFactor);

        if (!$summary) {
            $builder->groupBy('o.number');

            if (!empty($order)) {
                $builder->addOrderBy($order);
            }
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int                $partnerId
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param float|int          $userCurrencyFactor
     *
     * @return \Doctrine\ORM\Query
     */
    public function getStatisticChartQuery($partnerId, $fromDate, $toDate, $userCurrencyFactor = 1)
    {
        $builder = $this->getStatisticChartQueryBuilder($partnerId, $fromDate, $toDate, $userCurrencyFactor);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getStatisticChartQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int                $partnerId
     * @param \DateTimeInterface $fromDate
     * @param \DateTimeInterface $toDate
     * @param float|int          $userCurrencyFactor
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getStatisticChartQueryBuilder($partnerId, $fromDate, $toDate, $userCurrencyFactor = 1)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'o.orderTime as date',
                'DATE_FORMAT(o.orderTime,\'%Y-%V\') as timeScale',
                'SUM((o.invoiceAmountNet - o.invoiceShippingNet) / (o.currencyFactor / :userCurrencyFactor)) as netTurnOver',
                'SUM((o.invoiceAmountNet - o.invoiceShippingNet) / (o.currencyFactor / :userCurrencyFactor) / 100 * partner.percent) as provision',
            ])
            ->from(\Shopware\Models\Order\Order::class, 'o')
            ->leftJoin('o.partner', 'partner')
            ->where('partner.id = ?0')
            ->andWhere('o.status != 4')
            ->andWhere('o.status != -1')
            ->andWhere('o.orderTime > ?1')
            ->andWhere('o.orderTime < ?2')
            ->groupBy('timeScale');

        $builder->setParameter(0, $partnerId);
        $builder->setParameter(1, $fromDate);
        $builder->setParameter(2, $toDate);
        $builder->setParameter('userCurrencyFactor', $userCurrencyFactor);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object to map the customer to the partner account
     *
     * @param string $mappingValue
     *
     * @return \Doctrine\ORM\Query
     */
    public function getCustomerForMappingQuery($mappingValue)
    {
        $builder = $this->getCustomerForMappingQueryBuilder($mappingValue);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getCustomerForMappingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $mappingValue
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCustomerForMappingQueryBuilder($mappingValue)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'customer.id as id',
            'customer.number as customerNumber',
            "CONCAT(CONCAT(customer.firstname, ' '), customer.lastname) as fullName",
            'billing.company as company',
            'customer.email as email',
        ]);
        $builder->from(\Shopware\Models\Customer\Customer::class, 'customer')
                ->leftJoin('customer.defaultBillingAddress', 'billing')
                ->where('customer.accountMode = 0')
                ->andWhere('customer.email = ?0')
                ->orWhere('customer.number = ?1')
                ->orWhere('customer.id = ?2');
        $builder->setParameter(0, $mappingValue);
        $builder->setParameter(1, $mappingValue);
        $builder->setParameter(2, $mappingValue);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object to validate the Tracking-Code because the Tracking-Code has to be unique
     *
     * @param string $trackingCode
     * @param int    $partnerId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getValidateTrackingCodeQuery($trackingCode, $partnerId)
    {
        $builder = $this->getValidateTrackingCodeQueryBuilder($trackingCode, $partnerId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateTrackingCodeQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $trackingCode
     * @param int    $partnerId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateTrackingCodeQueryBuilder($trackingCode, $partnerId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(
            [
                'partner.id',
            ])
            ->from(\Shopware\Models\Partner\Partner::class, 'partner')
            ->where('partner.idCode = ?1')
            ->andWhere('partner.id != ?2')
            ->setParameter(1, $trackingCode)
            ->setParameter(2, $partnerId);

        return $builder;
    }

    /**
     * Helper method to get the date part of the dql query
     *
     * @param string $alias
     * @param bool   $monthlyAmount | whether to add the selection of a month or not
     *
     * @return string
     */
    private function getDatePartListDQL($alias, $monthlyAmount = false)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->from(\Shopware\Models\Order\Order::class, $alias)
                ->select(['SUM(' . $alias . '.invoiceAmountNet - ' . $alias . '.invoiceShippingNet)'])
                ->where('DATE_FORMAT(CURRENT_DATE(),\'%Y\') = DATE_FORMAT(' . $alias . '.orderTime,\'%Y\')')
                ->andWhere($alias . '.status NOT IN(\'4\', \'-1\')')
                ->andWhere($alias . '.partnerId = partner.idCode');
        if ($monthlyAmount) {
            $builder->andWhere('DATE_FORMAT(CURRENT_DATE(),\'%m\') = DATE_FORMAT(' . $alias . '.orderTime,\'%m\')');
        }

        return $builder->getDQL();
    }
}
