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

namespace Shopware\Components\Model;

use Doctrine\ORM\Query\Expr\Comparison;
use InvalidArgumentException;

class QueryOperatorValidator
{
    /**
     * Taken from https://dev.mysql.com/doc/refman/8.0/en/non-typed-operators.html
     *
     * @var string[]
     */
    private $validOperators = [
        '&',
        '&&',
        '%',
        '~',
        '!',
        '!=',
        '-',
        '+',
        '*',
        '|',
        '||',
        '^',
        '/',
        '<=>',
        '->',
        '->>',
        '>>',
        '<<',
        'AND',
        'BETWEEN',
        'BINARY',
        'CASE',
        'CONTAINS',
        'DIV',
        'IN',
        'ISNULL',
        'IS NULL',
        'EQ',
        'LIKE',
        'MOD',
        'OR',
        'REGEXP',
        'RLIKE',
        'SOUNDS LIKE',
        'STARTS_WITH',
        'ENDS_WITH',
        'XOR',
        Comparison::EQ,  // =
        Comparison::NEQ, // <>
        Comparison::LT,  // <
        Comparison::LTE, // <=
        Comparison::GT,  // >
        Comparison::GTE, // >=
    ];

    /**
     * @param string[] $validOperators
     */
    public function __construct(array $validOperators = [])
    {
        $this->validOperators = \array_merge(
            $this->validOperators,
            \array_map('strtoupper', $validOperators)
        );
    }

    /**
     * @param string $operator
     *
     * @return bool
     */
    public function isValid($operator)
    {
        if (\in_array(
            \trim(\str_replace([' ', 'NOT'], '', \strtoupper($operator))
            ), $this->validOperators, true)
        ) {
            return true;
        }

        throw new InvalidArgumentException(\sprintf("'%s' is no valid operator", $operator));
    }
}
