<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace   Shopware\Models\Payment;
use         Shopware\Components\Model\ModelRepository,
            Doctrine\ORM\Query\Expr;
/**
 * Shopware Payment Model
 *
 * The repository builds the query to read the payments.
 */
class Repository extends ModelRepository
{

    /**
     * Returns a query-object for all known payments
     *
     * @param null $filter
     * @param null $order
     * @param null $offset
     * @param null $limit
     * @param bool $onlyActive
     * @return \Doctrine\ORM\Query
     */
     public function getPaymentsQuery($filter = null, $order = null, $offset = null, $limit = null, $onlyActive = true)
     {
         $builder = $this->getPaymentsQueryBuilder($filter, $order, $onlyActive);
         if ($limit !== null) {
             $builder->setFirstResult($offset)
                     ->setMaxResults($limit);
         }
         return $builder->getQuery();
     }

    /**
     * Helper method to create the query builder for the "getPaymentsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $order
     * @param bool $onlyActive
     * @return \Doctrine\ORM\QueryBuilder
     */
     public function getPaymentsQueryBuilder($filter = null, $order = null, $onlyActive = true)
     {
         $builder = $this->createQueryBuilder('p');
         $builder->select(array(
             'p.id as id',
             'p.name as name',
             'p.description as description',
             'p.position as position',
             'p.active as active'
         ));

         if($onlyActive) {
             $builder->where('p.active = 1');
         }

         if ($filter !== null) {
             $builder->addFilter($filter);
         }
         if ($order !== null) {
             $builder->addOrderBy($order);
         }

         return $builder;
     }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery()
    {
        $builder = $this->getListQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('payment', 'countries', 'shops', 'attribute')
                ->from($this->getEntityName(), 'payment')
                ->leftJoin('payment.countries', 'countries')
                ->leftJoin('payment.attribute', 'attribute')
                ->leftJoin('payment.shops', 'shops');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $paymentId
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($paymentId)
    {
        $builder = $this->getAttributesQueryBuilder($paymentId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $paymentId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($paymentId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Payment', 'attribute')
                      ->where('attribute.paymentId = ?1')
                      ->setParameter(1, $paymentId);
        return $builder;
    }

}
