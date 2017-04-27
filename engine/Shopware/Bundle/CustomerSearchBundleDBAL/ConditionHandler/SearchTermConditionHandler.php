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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\Condition\SearchTermCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

class SearchTermConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof SearchTermCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $fields = [
            'customer.email',
            'customer.title',
            'customer.salutation',
            'customer.firstname',
            'customer.lastname',
            'customer.birthday',
            'customer.customernumber',
            'customer.company',
            'customer.department',
            'customer.street',
            'customer.zipcode',
            'customer.city',
            'customer.phone',
            'customer.additional_address_line1',
            'customer.additional_address_line2',
            'customer.first_order_time',
            'customer.last_order_time',
            'customer.products',
        ];
        $where = array_map(function ($field) {
            return $field . ' LIKE :searchTerm';
        }, $fields);

        /* @var SearchTermCondition $condition */
        $query->andWhere(implode(' OR ', $where));
        $query->setParameter(':searchTerm', '%' . $condition->getTerm() . '%');
    }
}
