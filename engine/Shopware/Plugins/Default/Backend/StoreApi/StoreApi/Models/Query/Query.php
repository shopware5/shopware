<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

abstract class Shopware_StoreApi_Models_Query_Query extends Enlight_Class
{
    const ORDER_DIRECTION_ASC = 'asc',
          ORDER_DIRECTION_DESC = 'desc';

    protected $validOrderBy = array();

    protected $validOrderDirection = array(
        self::ORDER_DIRECTION_ASC,
        self::ORDER_DIRECTION_DESC
    );

    protected $validCriterion = array();

    protected $start = 0;
    protected $limit = null;
    protected $orderBy = null;
    protected $orderDirection = null;

    protected $criterion = array();

    /**
     * @param Shopware_StoreApi_Models_Query_Criterion_Criterion|array $criterion
     * @return \Shopware_StoreApi_Models_Query_Query
     */
    public function addCriterion($criterion)
    {
        if ($criterion instanceof Shopware_StoreApi_Models_Query_Criterion_Criterion) {
            $this->criterion[] = $criterion;
        } elseif (is_array($criterion)) {
            foreach($criterion as $item)
            $this->addCriterion($item);
        }

        return $this;
    }

    public function getCriterion()
    {
        $criterion_collection = array();

        if (empty($this->criterion)) {
            return $criterion_collection;
        }

        foreach ($this->criterion as $criterion) {
            if (in_array(get_class($criterion), $this->validCriterion)) {
                $statement = $criterion->getCriterionStatement();
                if (!empty($statement)) {
                    $criterion_collection = array_merge($statement, $criterion_collection);
                }
            }
        }

        return $criterion_collection;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOrderBy($orderBy)
    {
        if (in_array($orderBy, $this->validOrderBy)) {
            $this->orderBy = $orderBy;
        }

        return $this;
    }

    public function setOrderDirection($orderDirection)
    {
        if (in_array($orderDirection, $this->validOrderDirection)) {
            $this->orderDirection = $orderDirection;
        }

        return $this;
    }
}
