<?php

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Shopware\Bundle\ESIndexingBundle\Struct\Product;

interface ProductModifierInterface
{
    const SERVICE_TAG_ID = 'shopware_elastic_search.product_provider.modifier';

    /**
     * Determine if requirements are given for applying the modifier to a product
     *
     * @param Product $product
     *
     * @return bool
     */
    public function supports(Product $product);

    /**
     * Modifies a single product and returns the changed product
     *
     * @param Product $product
     *
     * @return Product
     */
    public function modify(Product $product);
}
