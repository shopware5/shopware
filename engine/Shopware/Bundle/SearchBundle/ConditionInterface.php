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

namespace Shopware\Bundle\SearchBundle;

/**
 * Defines a condition which can be added to the
 * \Shopware\Bundle\SearchBundle\Criteria class.
 *
 * Each condition is handled by his own condition handler
 * which defined in the specify gateway engines.
 */
interface ConditionInterface extends CriteriaPartInterface
{
    const OPERATOR_EQ = '=';
    const OPERATOR_NEQ = '!=';
    const OPERATOR_LT = '<';
    const OPERATOR_LTE = '<=';
    const OPERATOR_GT = '>';
    const OPERATOR_GTE = '>=';
    const OPERATOR_NOT_IN = 'NOT IN';
    const OPERATOR_IN = 'IN';
    const OPERATOR_BETWEEN = 'BETWEEN';
    const OPERATOR_STARTS_WITH = 'STARTS_WITH';
    const OPERATOR_ENDS_WITH = 'ENDS_WITH';
    const OPERATOR_CONTAINS = 'CONTAINS';
}
