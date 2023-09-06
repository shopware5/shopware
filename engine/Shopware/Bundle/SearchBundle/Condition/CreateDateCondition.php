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

class CreateDateCondition implements ConditionInterface, JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    private const NAME = 'create_date_condition';

    /**
     * @var int
     */
    protected $days;

    /**
     * @param int $days
     */
    public function __construct($days)
    {
        Assertion::integerish($days);
        $this->days = (int) $days;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return int
     */
    public function getDays()
    {
        return $this->days;
    }
}
