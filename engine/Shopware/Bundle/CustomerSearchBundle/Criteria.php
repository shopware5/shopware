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

namespace Shopware\Bundle\CustomerSearchBundle;

use Shopware\Bundle\SearchBundle\ConditionInterface;

class Criteria implements \JsonSerializable
{
    /**
     * Offset for the limitation
     * @var int|null
     */
    protected $offset;

    /**
     * Count of result
     * @var int|null
     */
    protected $limit;

    /**
     * @var ConditionInterface[]
     */
    protected $conditions = [];

    /**
     * @var bool
     */
    protected $fetchTotal = true;

    /**
     * @param int|null $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param int|null $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return ConditionInterface[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param ConditionInterface $condition
     * @return $this
     */
    public function addCondition(ConditionInterface $condition)
    {
        $this->conditions[$condition->getName()] = $condition;
        return $this;
    }

    /**
     * @return bool
     */
    public function fetchTotal()
    {
        return $this->fetchTotal;
    }

    /**
     * @param bool $fetchTotal
     */
    public function setFetchTotal($fetchTotal)
    {
        $this->fetchTotal = $fetchTotal;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
