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

namespace Shopware\Bundle\SearchBundle\DBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\DBAL\ConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Components\Model\DBAL\QueryBuilder;

class CategoryConditionHandler implements ConditionHandlerInterface
{
    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param ConditionInterface $condition
     * @return bool
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof CategoryCondition);
    }

    /**
     * Extends the query with a category condition.
     * The passed category condition contains an array of multiple category ids.
     * The searched product has to be in one of the passed categories.
     *
     * @param ConditionInterface|CategoryCondition $condition
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        Context $context
    ) {
        $query->innerJoin(
            'product',
            's_articles_categories_ro',
            'productCategory',
            'productCategory.articleID = product.id
             AND productCategory.categoryID IN (:category)'
        );

        $query->setParameter(
            ':category',
            $condition->getCategoryIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
