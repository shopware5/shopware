<?php

namespace Shopware\Struct\Product;

use Shopware\Struct\Customer\Group;
use Shopware\Struct\Extendable;

/**
 * @package Shopware\Struct
 */
class Price extends Extendable
{
    /**
     * Contains the calculated gross or net price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price
     *
     * @var float
     */
    private $calculatedPrice;

    /**
     * Contains the calculated reference unit price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price.
     *
     * The reference unit price is calculated over the price value
     * and the pack and reference unit of the product.
     *
     * @var float
     */
    private $calculatedReferencePrice;

    /**
     * Contains the calculated pseudo price.
     *
     * This price will be set from the Shopware price service class
     * \Shopware\Service\Price.
     *
     * The pseudo price is used to fake a discount in the store front
     * without defining a global discount for a customer group.
     *
     * @var float
     */
    private $calculatedPseudoPrice;

    /**
     * @var PriceRule
     */
    private $rule;

    /**
     * @param PriceRule $rule
     */
    function __construct(PriceRule $rule)
    {
        $this->rule = $rule;
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
     * @return \Shopware\Struct\Product\PriceRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return \Shopware\Struct\Product\Unit
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
}
