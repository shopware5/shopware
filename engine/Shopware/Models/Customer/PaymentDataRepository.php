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

namespace Shopware\Models\Customer;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

/**
 * Shopware Payment Data Repository
 *
 * @extends ModelRepository<PaymentData>
 */
class PaymentDataRepository extends ModelRepository
{
    /**
     * @param int|null $userId
     * @param string   $paymentName
     *
     * @return QueryBuilder
     */
    public function getCurrentPaymentDataQueryBuilder($userId, $paymentName)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['paymentdata']);
        $builder->from(PaymentData::class, 'paymentdata')
            ->leftJoin('paymentdata.paymentMean', 'paymentmean')
            ->leftJoin('paymentdata.customer', 'customer')
            ->where('customer.id = :userId')
            ->andWhere('paymentmean.name = :paymentName')
            ->andWhere('paymentmean.active = 1')
            ->setParameter('userId', $userId)
            ->setParameter('paymentName', $paymentName);

        return $builder;
    }
}
