<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface ViewLineItemTransformerInterface
{
    /**
     * @param CalculatedCart $cart
     * @param ViewCart $templateCart
     * @param ShopContextInterface $context
     */
    public function transform(
        CalculatedCart $cart,
        ViewCart $templateCart,
        ShopContextInterface $context
    );
}
