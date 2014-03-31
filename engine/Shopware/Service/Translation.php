<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;

class Translation
{
    /**
     * @param Struct\ProductMini $productMini
     */
    public function translateProduct(Struct\ProductMini $productMini)
    {
        $productMini->addState(
            Struct\ProductMini::STATE_TRANSLATED
        );
    }
}