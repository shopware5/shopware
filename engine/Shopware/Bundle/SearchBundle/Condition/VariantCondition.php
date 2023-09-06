<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SearchBundle\Condition;

use Assert\Assertion;
use JsonSerializable;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class VariantCondition implements ConditionInterface, JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * Each value id is combined via OR expression to restrict the criteria.
     *
     * @var int[]
     */
    protected $optionIds;

    /**
     * @var bool
     */
    protected $expandVariants;

    /**
     * @var int
     */
    protected $groupId;

    /**
     * @param int[] $optionIds
     * @param bool  $expandVariants
     * @param int   $groupId
     */
    public function __construct(array $optionIds, $expandVariants, $groupId)
    {
        Assertion::allIntegerish($optionIds);
        $this->optionIds = array_map('intval', $optionIds);
        sort($this->optionIds, SORT_NUMERIC);
        $this->expandVariants = $expandVariants;
        $this->groupId = (int) $groupId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'options_' . implode('_', $this->getOptionIds());
    }

    /**
     * @return int[]
     */
    public function getOptionIds()
    {
        return $this->optionIds;
    }

    public function expandVariants()
    {
        return $this->expandVariants;
    }

    public function getGroupId()
    {
        return $this->groupId;
    }
}
