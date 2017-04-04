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

namespace Shopware\Bundle\CartBundle\Infrastructure\Validator\Data;

use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleData;

class ProductAttributeRuleData extends RuleData
{
    /**
     * e.g 'attr1' => ['value1', 'value2'], 'attr2' => ['value1', 'value2']
     *
     * @var array[]
     */
    private $data;

    /**
     * @param array[] $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function hasAttributeValue($attribute, $value)
    {
        if (!array_key_exists($attribute, $this->data)) {
            return false;
        }

        return in_array($value, $this->data[$attribute]);
    }
}
