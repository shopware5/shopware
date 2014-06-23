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

namespace Shopware\Gateway\DBAL\ConditionHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;

/**
 * @package Shopware\Gateway\DBAL\ConditionHandler
 */
interface DBAL
{
    /**
     * Checks if the passed condition can be handled by this class.
     *
     * @param Condition $condition
     * @return bool
     */
    public function supportsCondition(Condition $condition);

    /**
     * Checks if the passed sorting can be handled by this class
     * @param Sorting $sorting
     * @return bool
     */
    public function supportsSorting(Sorting $sorting);

    /**
     * Handles the passed condition object.
     * Extends the provided query builder with the specify conditions.
     * Should use the andWhere function, otherwise other conditions would be overwritten.
     *
     * @param Condition $condition
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateCondition(
        Condition $condition,
        QueryBuilder $query,
        Context $context
    );

    /**
     * Handles the passed sorting object.
     * Extends the passed query builder with the specify sorting.
     * Should use the addOrderBy function, otherwise other sortings would be overwritten.
     *
     * @param Sorting $sorting
     * @param QueryBuilder $query
     * @param Context $context
     * @return void
     */
    public function generateSorting(
        Sorting $sorting,
        QueryBuilder $query,
        Context $context
    );
}
