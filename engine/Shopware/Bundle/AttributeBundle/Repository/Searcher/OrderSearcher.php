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

namespace Shopware\Bundle\AttributeBundle\Repository\Searcher;

use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Models\Order\Order;

class OrderSearcher extends GenericSearcher
{
    /**
     * {@inheritdoc}
     */
    protected function createQuery(SearchCriteria $criteria)
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select($this->getIdentifierField());
        $query->from(Order::class, 'entity', $this->getIdentifierField());
        $query->leftJoin('entity.payment', 'payment');
        $query->leftJoin('entity.dispatch', 'dispatch');
        $query->leftJoin('entity.shop', 'shop');
        $query->leftJoin('entity.billing', 'billing');
        $query->leftJoin('entity.customer', 'customer');
        $query->leftJoin('entity.documents', 'document');
        $query->leftJoin('billing.country', 'billingCountry');
        $query->setAlias('entity');

        return $query;
    }

    /**
     * @return array
     */
    protected function getSearchFields(SearchCriteria $criteria)
    {
        return [
            'entity.number^2',
            'customer.email^2',
            'customer.firstname^3',
            'customer.lastname^3',
            'billing.zipCode^0.5',
            'billing.city^0.5',
            'billing.company^0.5',
            'document.documentId^3',
        ];
    }
}
