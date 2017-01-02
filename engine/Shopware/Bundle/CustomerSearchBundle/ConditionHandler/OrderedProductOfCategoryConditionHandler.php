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

namespace Shopware\Bundle\CustomerSearchBundle\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\CustomerSearchBundle\Condition\OrderedProductOfCategoryCondition;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class OrderedProductOfCategoryConditionHandler implements ConditionHandlerInterface
{
    public function supports(ConditionInterface $condition)
    {
        return $condition instanceof OrderedProductOfCategoryCondition;
    }

    public function handle(ConditionInterface $condition, QueryBuilder $query)
    {
        $query->innerJoin(
            'customer',
            's_order',
            'orderedCategory',
            'orderedCategory.userID = customer.id'
        );

        $query->innerJoin(
            'orderedCategory',
            's_order_details',
            'orderedCategoryDetails',
            'orderedCategoryDetails.orderID = orderedCategory.id
             AND orderedCategoryDetails.modus = 0'
        );

        $query->innerJoin(
            'orderedCategoryDetails',
            's_articles_categories_ro',
            'orderedCategoryMapping',
            'orderedCategoryMapping.articleID = orderedCategoryDetails.articleID
            AND orderedCategoryMapping.categoryID IN (:OrderedProductOfCategoryCondition)'
        );

        /** @var OrderedProductOfCategoryCondition $condition */
        $query->setParameter(':OrderedProductOfCategoryCondition', $condition->getCategoryIds(), Connection::PARAM_INT_ARRAY);
    }
}
