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

namespace   Shopware\Models\Customer;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository for the billing model (Shopware\Models\Customer\Billing).
 *
 * The billing model repository is responsible to load all billing data.
 * It supports the standard functions like findAll or findBy and extends the standard repository for
 * some specific functions to return the model data as array.
 */
class BillingRepository extends ModelRepository
{
    /**
     * Returns a query-object for the billing address for a specified user
     *
     * @param null $userId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUserBillingQuery($userId)
    {
        $builder = $this->getUserBillingQueryBuilder($userId);

        return $builder->getQuery();
    }

    /**
     * Helper method to create the query builder for the "getUserBillingQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $userId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserBillingQueryBuilder($userId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'billing.firstName',
            'billing.lastName',
            'billing.street',
            'billing.zipCode',
            'billing.city',
        ]);

        $builder->from('Shopware\Models\Customer\Billing', 'billing')
                ->where('IDENTITY(billing.customer) = ?1')
                ->setParameter(1, $userId);

        return $builder;
    }
}
