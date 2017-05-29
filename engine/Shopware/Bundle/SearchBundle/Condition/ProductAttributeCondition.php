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

namespace Shopware\Bundle\SearchBundle\Condition;

use Assert\Assertion;
use Shopware\Bundle\SearchBundle\ConditionInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductAttributeCondition implements ConditionInterface
{
    const OPERATOR_EQ = '=';
    const OPERATOR_NEQ = '!=';
    const OPERATOR_LT = '<';
    const OPERATOR_LTE = '<=';
    const OPERATOR_BETWEEN = 'BETWEEN';
    const OPERATOR_GT = '>';
    const OPERATOR_GTE = '>=';
    const OPERATOR_IN = 'IN';
    const OPERATOR_STARTS_WITH = 'STARTS_WITH';
    const OPERATOR_ENDS_WITH = 'ENDS_WITH';
    const OPERATOR_CONTAINS = 'CONTAINS';

    /**
     * @var string
     */
    private $field;

    /**
     * @var string|array
     */
    private $value;

    /**
     * @var string
     */
    private $operator;

    /**
     * @param string       $field
     * @param string       $operator
     * @param string|array $value    ['min' => 1, 'max' => 10] for between operator
     */
    public function __construct($field, $operator, $value)
    {
        Assertion::string($field);
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product_attribute_' . $this->field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }
}
