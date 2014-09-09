<?php

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

/**
 * Interface ProductContextInterface
 * @package Shopware\Bundle\StoreFrontBundle\Struct
 */
interface ProductContextInterface extends ShopContextInterface
{
    /**
     * Returns all tax rules
     * @return Tax[]
     */
    public function getTaxRules();

    /**
     * Returns the active tax rule for the provided tax id.
     * @param $taxId
     * @return Tax
     */
    public function getTaxRule($taxId);

    /**
     * Returns the active price groups
     * @return PriceGroup[]
     */
    public function getPriceGroups();
}