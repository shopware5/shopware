<?php

namespace Shopware\Gateway;

use Shopware\Struct as Struct;

interface Media
{
    /**
     * Returns the product preview image, which used
     * as product cover in listings or on the detail page.
     *
     * The preview image has the flag "main = 1" in the database.
     *
     * @param \Shopware\Struct\ListProduct $product
     * @return \Shopware\Struct\Media
     */
    public function getProductCover(Struct\ListProduct $product);
}