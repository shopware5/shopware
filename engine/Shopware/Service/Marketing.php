<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;

interface Marketing
{
    /**
     * Builds a \Shopware\Struct\Product\MarketingAttribute object,
     * which contains additionally marketing data about the product.
     *
     * @param Struct\ListProduct $product
     * @return Struct\Product\MarketingAttribute
     */
    public function getProductAttribute(Struct\ListProduct $product);
}