<?php

namespace Shopware\Gateway;

use Shopware\Struct as Struct;

interface Product
{
    /**
     * Returns a mini struct of a product.
     *
     * This function is used for listings, search results
     * or sliders.
     *
     * @param $number
     * @return Struct\ProductMini
     */
    public function getMini($number);

    /**
     * The get function returns a full product struct.
     *
     * This function should only be used if all product data
     * are required, like on the article detail page.
     *
     * @param $number
     * @return Struct\Product
     */
    public function get($number);
}