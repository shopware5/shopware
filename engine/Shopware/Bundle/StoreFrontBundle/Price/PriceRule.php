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

namespace Shopware\Bundle\StoreFrontBundle\Price;

use Shopware\Bundle\StoreFrontBundle\Common\Struct;
use Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup;
use Shopware\Bundle\StoreFrontBundle\Unit\Unit;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceRule extends Struct
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Price value of the product price struct.
     *
     * @var float
     */
    protected $price;

    /**
     * @var int
     */
    protected $from;

    /**
     * @var null|int
     */
    protected $to;

    /**
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    protected $pseudoPrice;

    /**
     * Contains the associated customer group of this price.
     * Each graduated product price is defined for a single customer group.
     *
     * @var CustomerGroup
     */
    protected $customerGroup;

    /**
     * @var Unit
     */
    protected $unit;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int|null $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @return int|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $pseudoPrice
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Unit\Unit $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Unit\Unit
     */
    public function getUnit()
    {
        return $this->unit;
    }
}
