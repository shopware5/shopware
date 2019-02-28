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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class VoteAverage extends Extendable
{
    /**
     * @var int
     */
    protected $count;

    /**
     * @var float
     */
    protected $average;

    /**
     * @var array
     */
    protected $pointCount;

    /**
     * @param float $average
     */
    public function setAverage($average)
    {
        $this->average = $average;
    }

    /**
     * @return float
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getPointCount()
    {
        return $this->pointCount;
    }

    /**
     * @param array $pointCount
     */
    public function setPointCount($pointCount)
    {
        $this->pointCount = $pointCount;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
