<?php

namespace Shopware\Gateway;

use Shopware\Struct;

/**
 * Class Product gateway.
 *
 * @package Shopware\Gateway\DBAL
 */
interface ListProduct
{
    /**
     * Returns a single of ListProduct struct which can be used for listings
     * or sliders.
     *
     * A mini product contains only the minified product data.
     * The mini data contains data sources:
     *  - article
     *  - variant
     *  - unit
     *  - attribute
     *  - tax
     *  - manufacturer
     *  - price group
     *
     * @param $number
     * @param Struct\Context $context
     * @return Struct\ListProduct
     */
    public function getListProduct($number, Struct\Context $context);

    /**
     * Returns a list of ListProduct structs which can be used for listings
     * or sliders.
     *
     * A mini product contains only the minified product data.
     * The mini data contains data sources:
     *  - article
     *  - variant
     *  - unit
     *  - attribute
     *  - tax
     *  - manufacturer
     *  - price group
     *
     * @param array $numbers
     * @param Struct\Context $context
     * @return Struct\ListProduct[]
     */
    public function getListProducts(array $numbers, Struct\Context $context);
}