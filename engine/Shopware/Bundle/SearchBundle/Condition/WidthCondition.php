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

use JsonSerializable;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class WidthCondition implements ConditionInterface, JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    private const NAME = 'width';

    /**
     * @var float|null
     */
    protected $minWidth;

    /**
     * @var float|null
     */
    protected $maxWidth;

    /**
     * @param float|null $minWidth
     * @param float|null $maxWidth
     */
    public function __construct($minWidth = null, $maxWidth = null)
    {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return float|null
     */
    public function getMinWidth()
    {
        return $this->minWidth;
    }

    /**
     * @return float|null
     */
    public function getMaxWidth()
    {
        return $this->maxWidth;
    }
}
