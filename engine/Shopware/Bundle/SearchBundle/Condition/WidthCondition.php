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

use Shopware\Bundle\SearchBundle\ConditionInterface;

class WidthCondition implements ConditionInterface, \JsonSerializable
{
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

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
