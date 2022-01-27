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
use JsonSerializable;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class ProductAttributeCondition implements ConditionInterface, JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var array|int|string|null
     */
    protected $value;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @param string                $field
     * @param string                $operator
     * @param array|int|string|null $value    ['min' => 1, 'max' => 10] for between operator
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
     * @return array|int|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|int|string|null $value
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
