<?php

namespace Shopware\Service;

use Shopware\Struct\ProductMini;

class Price
{
    public function calculateProductMini(ProductMini $product)
    {
        $product->addState(
            ProductMini::STATE_PRICE_CALCULATED
        );
    }
}