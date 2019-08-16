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

use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class Price extends Extendable
{
    /**
     * Contains the calculated gross or net price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Bundle\StoreFrontBundle\Service\Price
     *
     * @var float
     */
    protected $calculatedPrice;

    /**
     * Contains the calculated reference unit price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Bundle\StoreFrontBundle\Service\Price.
     *
     * The reference unit price is calculated over the price value
     * and the pack and reference unit of the product.
     *
     * @var float
     */
    protected $calculatedReferencePrice;

    /**
     * Contains the calculated pseudo price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Bundle\StoreFrontBundle\Service\Price.
     *
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    protected $calculatedPseudoPrice;

    /**
     * @var PriceRule
     */
    protected $rule;

    public function __construct(PriceRule $rule)
    {
        $this->rule = $rule;
        $this->attributes = $rule->getAttributes();
    }

    /**
     * @param float $calculatedPrice
     */
    public function setCalculatedPrice($calculatedPrice)
    {
        $this->calculatedPrice = $calculatedPrice;
    }

    /**
     * @return float
     */
    public function getCalculatedPrice()
    {
        return $this->calculatedPrice;
    }

    /**
     * @param float $calculatedPseudoPrice
     */
    public function setCalculatedPseudoPrice($calculatedPseudoPrice)
    {
        $this->calculatedPseudoPrice = $calculatedPseudoPrice;
    }

    /**
     * @return float
     */
    public function getCalculatedPseudoPrice()
    {
        return $this->calculatedPseudoPrice;
    }

    /**
     * @param float $calculatedReferencePrice
     */
    public function setCalculatedReferencePrice($calculatedReferencePrice)
    {
        $this->calculatedReferencePrice = $calculatedReferencePrice;
    }

    /**
     * @return float
     */
    public function getCalculatedReferencePrice()
    {
        return $this->calculatedReferencePrice;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    public function setRule(PriceRule $rule = null)
    {
        $this->rule = $rule;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Product\Unit|null
     */
    public function getUnit()
    {
        return $this->rule->getUnit();
    }

    /**
     * @return Group
     */
    public function getCustomerGroup()
    {
        return $this->rule->getCustomerGroup();
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->rule->getFrom();
    }

    /**
     * @return int|null
     */
    public function getTo()
    {
        return $this->rule->getTo();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
