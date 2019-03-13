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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition as Condition;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

trait DynamicConditionParserTrait
{
    /**
     * Adds base conditions to a query builder object
     * NOTE: The method will also verify that the column to be compared actually exists in the table.
     *
     * @param string            $table      table name
     * @param string            $tableAlias table alias in query
     * @param string            $field      Field to be used in the comparisons
     * @param array|string|null $value
     * @param string|null       $operator
     *
     * @throws \InvalidArgumentException If field value is empty                          (code: 1)
     * @throws \InvalidArgumentException If an invalid column name has been specified     (code: 2)
     * @throws \InvalidArgumentException If an unsupported operator has been specified    (code: 3)
     * @throws \RuntimeException         If columns could not be retrieved from the table
     */
    public function parse(QueryBuilder $query, $table, $tableAlias, $field = null, $value = null, $operator = null)
    {
        $field = trim($field);

        if (empty($field)) {
            throw new \InvalidArgumentException('Condition class requires a defined attribute field!', 1);
        }

        /**
         * Verify the table effectively has the column/field that is being queried
         */
        $columns = $query->getConnection()
            ->getSchemaManager()
            ->listTableColumns($table);

        if (empty($columns)) {
            throw new \RuntimeException(sprintf('Could not retrieve columns from "%s".', $table));
        }

        $names = array_map(function (\Doctrine\DBAL\Schema\Column $column) {
            return strtolower($column->getName());
        }, $columns);

        if (!array_key_exists(strtolower($field), $names)) {
            throw new \InvalidArgumentException(sprintf('Invalid column name "%s" specified.', $field), 1);
        }

        $validOperators = [
            Condition::OPERATOR_EQ,
            Condition::OPERATOR_NEQ,
            Condition::OPERATOR_LT,
            Condition::OPERATOR_LTE,
            Condition::OPERATOR_GT,
            Condition::OPERATOR_GTE,
            Condition::OPERATOR_NOT_IN,
            Condition::OPERATOR_IN,
            Condition::OPERATOR_BETWEEN,
            Condition::OPERATOR_STARTS_WITH,
            Condition::OPERATOR_ENDS_WITH,
            Condition::OPERATOR_CONTAINS,
        ];

        // Normalize with strtoupper in case of non-algorithmic comparisons NOT IN, IN, STARTS WITH
        $operator = strtoupper(trim($operator));

        /*
         * When an operator is not specified, by default, return all results that are not null
         */
        if (empty($operator)) {
            throw new \InvalidArgumentException(
                sprintf('Must specify an operator, please use one of: %s', implode(', ', $validOperators)),
                3
            );
        }

        //Identify each field placeholder value with table alias and a hash of condition properties
        $boundParamName = sprintf(':%s_%s', $tableAlias, md5($field . $operator . json_encode($value)));
        $field = sprintf('%s.%s', $tableAlias, $field);

        switch (true) {
            case $operator === Condition::OPERATOR_EQ:
                if ($value === null) {
                    $query->andWhere($query->expr()->isNull($field));
                    break;
                }
                $query->andWhere($query->expr()->eq($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_NEQ:
                if ($value === null) {
                    $query->andWhere($query->expr()->isNotNull($field));
                    break;
                }
                $query->andWhere($query->expr()->neq($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_LT:
                $query->andWhere($query->expr()->lt($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_LTE:
                $query->andWhere($query->expr()->lte($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_GT:
                $query->andWhere($query->expr()->gt($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_GTE:
                $query->andWhere($query->expr()->gte($field, $boundParamName));
                $query->setParameter($boundParamName, $value);
                break;

            case $operator === Condition::OPERATOR_NOT_IN:
                $query->andWhere($query->expr()->notIn($field, $boundParamName));
                $query->setParameter(
                    $boundParamName,
                    !is_array($value) ? [(string) $value] : $value,
                    Connection::PARAM_STR_ARRAY
                );
                break;

            case $operator === Condition::OPERATOR_IN:
                $query->andWhere($query->expr()->in($field, $boundParamName));
                $query->setParameter(
                    $boundParamName,
                    !is_array($value) ? [(string) $value] : $value,
                    Connection::PARAM_STR_ARRAY
                );
                break;

            case $operator === Condition::OPERATOR_BETWEEN:
                if (!isset($value['min']) && !isset($value['max'])) {
                    throw new \InvalidArgumentException('The between operator needs a minimum or a maximum', 3);
                }

                if (isset($value['min'])) {
                    $query->andWhere($query->expr()->gte($field, $boundParamName . 'Min'))
                        ->setParameter($boundParamName . 'Min', $value['min']);
                }

                if (isset($value['max'])) {
                    $query->andWhere($query->expr()->lte($field, $boundParamName . 'Max'))
                        ->setParameter($boundParamName . 'Max', $value['max']);
                }
                break;

            case $operator === Condition::OPERATOR_STARTS_WITH:
                $query->andWhere($query->expr()->like($field, $boundParamName));
                $query->setParameter($boundParamName, $value . '%');
                break;

            case $operator === Condition::OPERATOR_ENDS_WITH:
                $query->andWhere($query->expr()->like($field, $boundParamName));
                $query->setParameter($boundParamName, '%' . $value);
                break;

            case $operator === Condition::OPERATOR_CONTAINS:
                $query->andWhere($query->expr()->like($field, $boundParamName));
                $query->setParameter($boundParamName, '%' . $value . '%');
                break;

            default:
                throw new \InvalidArgumentException(
                    sprintf('Invalid operator specified, please use one of: %s', implode(', ', $validOperators)),
                    3
                );
                break;
        }
    }
}
