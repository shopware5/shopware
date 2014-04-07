<?php

namespace Shopware\Gateway;

use Shopware\Struct as Struct;

interface Property
{
    /**
     * Constant for the alphanumeric sort configuration of the category filters
     */
    const FILTERS_SORT_ALPHANUMERIC = 0;

    /**
     * Constant for the numeric sort configuration of the category filters
     */
    const FILTERS_SORT_NUMERIC = 1;

    /**
     * Constant for the article count sort configuration of the category filters
     */
    const FILTERS_SORT_ARTICLE_COUNT = 2;

    /**
     * Constant for the position sort configuration of the category filters
     */
    const FILTERS_SORT_POSITION = 3;

    /**
     * Returns the property set for the passed product.
     *
     * The property has to be loaded with all property groups
     * and values of the product.
     *
     * @param Struct\ProductMini $product
     * @return Struct\PropertySet
     */
    public function getProductSet(Struct\ProductMini $product);
}