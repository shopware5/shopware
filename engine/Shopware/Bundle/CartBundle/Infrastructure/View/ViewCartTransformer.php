<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ViewCartTransformer
{
    /**
     * @var ViewLineItemTransformerInterface[]
     */
    private $transformers = [];

    /**
     * @param ViewLineItemTransformerInterface[] $transformers
     */
    public function __construct(array $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @param CalculatedCart $calculatedCart
     * @param ShopContextInterface $context
     * @return ViewCart
     */
    public function transform(CalculatedCart $calculatedCart, ShopContextInterface $context)
    {
        $viewCart = ViewCart::createFromCalculatedCart($calculatedCart);

        foreach ($this->transformers as $transformer) {
            $transformer->transform($calculatedCart, $viewCart, $context);
        }

        return $viewCart;
    }
}
